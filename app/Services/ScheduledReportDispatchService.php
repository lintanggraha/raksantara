<?php

namespace App\Services;

use App\Exports\MultiSheetReportExport;
use App\Mail\ScheduledReportDelivered;
use App\ScheduledReport;
use App\ScheduledReportRun;
use App\User;
use App\Support\PdfReportLayout;
use App\Support\PdfSectionPaginator;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;

class ScheduledReportDispatchService
{
    public function parseRecipients(?string $rawRecipients, ?string $fallbackEmail = null): array
    {
        $parts = preg_split('/[,;]+/', (string) $rawRecipients);
        $emails = collect($parts ?: [])
            ->map(function ($item) {
                return strtolower(trim((string) $item));
            })
            ->filter(function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            })
            ->unique()
            ->values()
            ->all();

        if (empty($emails) && !empty($fallbackEmail) && filter_var($fallbackEmail, FILTER_VALIDATE_EMAIL)) {
            $emails[] = strtolower(trim((string) $fallbackEmail));
        }

        return $emails;
    }

    public function computeNextRunAt(
        string $frequency,
        string $sendTime,
        string $timezone,
        ?int $dayOfWeek = null,
        ?int $dayOfMonth = null,
        ?Carbon $from = null
    ): Carbon {
        $timezone = trim($timezone) !== '' ? $timezone : 'UTC';
        $now = ($from ?: now())->copy()->setTimezone($timezone);

        [$hour, $minute] = $this->parseTime($sendTime);

        $frequency = strtolower(trim($frequency));
        if ($frequency === 'weekly') {
            $targetDay = is_int($dayOfWeek) ? max(0, min(6, $dayOfWeek)) : 1;
            $candidate = $now->copy()->setTime($hour, $minute, 0);
            while ($candidate->dayOfWeek !== $targetDay) {
                $candidate->addDay();
            }
            if ($candidate->lessThanOrEqualTo($now)) {
                $candidate->addWeek();
            }

            return $candidate->copy()->setTimezone('UTC');
        }

        if ($frequency === 'monthly') {
            $targetDay = is_int($dayOfMonth) ? max(1, min(28, $dayOfMonth)) : 1;
            $candidate = $now->copy()->startOfMonth()->setDay($targetDay)->setTime($hour, $minute, 0);
            if ($candidate->lessThanOrEqualTo($now)) {
                $candidate->addMonthNoOverflow()->startOfMonth()->setDay($targetDay)->setTime($hour, $minute, 0);
            }

            return $candidate->copy()->setTimezone('UTC');
        }

        $candidate = $now->copy()->setTime($hour, $minute, 0);
        if ($candidate->lessThanOrEqualTo($now)) {
            $candidate->addDay();
        }

        return $candidate->copy()->setTimezone('UTC');
    }

    public function dispatchSchedule(
        ScheduledReport $schedule,
        ReportDataService $reportDataService,
        string $triggerType = 'auto'
    ): array {
        $executedAt = now();
        $triggerType = $this->normalizeTriggerType($triggerType);

        $filters = is_array($schedule->filters) ? $schedule->filters : [];
        $year = isset($filters['year']) && is_numeric($filters['year'])
            ? (int) $filters['year']
            : null;
        $reportFilters = [
            'risk_category_id' => $filters['risk_category_id'] ?? null,
            'risk_cause_category_id' => $filters['risk_cause_category_id'] ?? null,
            'risk_owner' => $filters['risk_owner'] ?? null,
        ];

        $scope = (string) $schedule->scope;
        $format = strtolower((string) $schedule->format);

        try {
            $payload = $reportDataService->buildPayload(
                $scope,
                $year,
                $reportFilters,
                $schedule->user instanceof User ? $schedule->user : null
            );
            $export = $this->buildExportBinary($payload, $format);
            $recipients = $this->resolveScheduleRecipients($schedule);

            if (empty($recipients)) {
                throw new \RuntimeException('No valid recipients configured.');
            }

            Mail::to($recipients)->send(new ScheduledReportDelivered(
                $schedule,
                (string) ($payload['title'] ?? 'Scheduled Report'),
                (string) ($export['filename'] ?? 'scheduled-report.pdf'),
                (string) ($export['binary'] ?? ''),
                (string) ($export['mime'] ?? 'application/octet-stream')
            ));

            $schedule->last_run_at = $executedAt;
            $schedule->next_run_at = $schedule->is_active
                ? $this->safeComputeNextRunAtForSchedule($schedule, $executedAt->copy()->addSecond())
                : null;
            $schedule->save();

            $this->recordRun(
                $schedule,
                $triggerType,
                'success',
                count($recipients),
                (string) ($export['filename'] ?? ''),
                null,
                $executedAt
            );

            return [
                'success' => true,
                'error' => null,
                'recipient_count' => count($recipients),
                'filename' => (string) ($export['filename'] ?? ''),
            ];
        } catch (\Throwable $e) {
            $schedule->next_run_at = $schedule->is_active
                ? $this->safeComputeNextRunAtForSchedule($schedule, $executedAt->copy()->addMinutes(1))
                : null;
            $schedule->save();

            $errorMessage = Str::limit((string) $e->getMessage(), 1000, '...');
            $this->recordRun(
                $schedule,
                $triggerType,
                'failed',
                0,
                null,
                $errorMessage,
                $executedAt
            );

            return [
                'success' => false,
                'error' => (string) $e->getMessage(),
                'recipient_count' => 0,
                'filename' => '',
            ];
        }
    }

    public function buildExportBinary(array $payload, string $format): array
    {
        $format = strtolower(trim($format));

        if ($format === 'xlsx' || $format === 'xls') {
            return $this->buildSpreadsheet($payload, $format);
        }

        if ($format === 'pdf') {
            return $this->buildPdf($payload);
        }

        if ($format === 'pptx') {
            return $this->buildPptx($payload);
        }

        throw new \InvalidArgumentException('Unsupported scheduled report format: ' . $format);
    }

    private function safeComputeNextRunAtForSchedule(ScheduledReport $schedule, Carbon $from): ?Carbon
    {
        try {
            return $this->computeNextRunAt(
                (string) $schedule->frequency,
                (string) $schedule->send_time,
                (string) $schedule->timezone,
                is_null($schedule->day_of_week) ? null : (int) $schedule->day_of_week,
                is_null($schedule->day_of_month) ? null : (int) $schedule->day_of_month,
                $from
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function buildSpreadsheet(array $payload, string $format = 'xlsx'): array
    {
        $sections = (array) ($payload['sections'] ?? []);
        array_unshift($sections, [
            'title' => 'Report Info',
            'headings' => ['Key', 'Value'],
            'rows' => [
                ['Title', (string) ($payload['title'] ?? 'Report')],
                ['Generated At', now()->format('Y-m-d H:i:s')],
                ['Section Count', (string) count($sections)],
            ],
        ]);

        $format = strtolower(trim($format));
        $isLegacyXls = $format === 'xls';
        $writerType = $isLegacyXls ? ExcelWriter::XLS : ExcelWriter::XLSX;
        $extension = $isLegacyXls ? 'xls' : 'xlsx';
        $mime = $isLegacyXls
            ? 'application/vnd.ms-excel'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        $binary = Excel::raw(
            new MultiSheetReportExport($sections),
            $writerType
        );

        return [
            'binary' => $binary,
            'filename' => $this->makeFilename((string) ($payload['file'] ?? 'scheduled-report'), $extension),
            'mime' => $mime,
        ];
    }

    private function buildPdf(array $payload): array
    {
        $sections = PdfSectionPaginator::paginate((array) ($payload['sections'] ?? []));
        $pdfLayout = PdfReportLayout::resolve($sections);

        $html = view('reports.pdf', [
            'title' => $payload['title'] ?? 'Report',
            'sections' => $sections,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'pdfLayout' => $pdfLayout,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $options->set('dpi', ($pdfLayout['density'] ?? 'normal') === 'ultra' ? 125 : 115);
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper(
            (string) ($pdfLayout['paper'] ?? 'A4'),
            (string) ($pdfLayout['orientation'] ?? 'landscape')
        );
        $dompdf->render();

        return [
            'binary' => $dompdf->output(),
            'filename' => $this->makeFilename((string) ($payload['file'] ?? 'scheduled-report'), 'pdf'),
            'mime' => 'application/pdf',
        ];
    }

    private function buildPptx(array $payload): array
    {
        $presentation = new PhpPresentation();
        $presentation->removeSlideByIndex(0);

        $generatedAt = now()->format('Y-m-d H:i:s');
        $title = (string) ($payload['title'] ?? 'Scheduled Report');

        $cover = $presentation->createSlide();
        $coverBackground = $cover->createRichTextShape()
            ->setHeight(720)
            ->setWidth(960)
            ->setOffsetX(0)
            ->setOffsetY(0);
        $coverBackground->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFF5F8FC');

        $coverHeader = $cover->createRichTextShape()
            ->setHeight(12)
            ->setWidth(960)
            ->setOffsetX(0)
            ->setOffsetY(0);
        $coverHeader->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF0F4C81');

        $coverTitle = $cover->createRichTextShape()
            ->setHeight(280)
            ->setWidth(900)
            ->setOffsetX(30)
            ->setOffsetY(130);
        $titleRun = $coverTitle->createTextRun($title);
        $titleRun->getFont()
            ->setBold(true)
            ->setSize(34)
            ->setColor(new Color('FF0F172A'));
        $coverTitle->createBreak();
        $coverTitle->createBreak();
        $metaRun = $coverTitle->createTextRun('Generated: ' . $generatedAt);
        $metaRun->getFont()
            ->setSize(16)
            ->setColor(new Color('FF334155'));

        $sections = is_array($payload['sections'] ?? null) ? $payload['sections'] : [];
        foreach ($sections as $section) {
            $this->appendPptxSectionSlides($presentation, $section);
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'rms-scheduled-pptx-');
        if ($temporaryPath === false) {
            throw new \RuntimeException('Unable to create temporary report file.');
        }

        $writer = IOFactory::createWriter($presentation, 'PowerPoint2007');
        $writer->save($temporaryPath);

        $binary = file_get_contents($temporaryPath);
        @unlink($temporaryPath);

        if ($binary === false) {
            throw new \RuntimeException('Unable to read generated PPTX report.');
        }

        return [
            'binary' => $binary,
            'filename' => $this->makeFilename((string) ($payload['file'] ?? 'scheduled-report'), 'pptx'),
            'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
    }

    private function appendPptxSectionSlides(PhpPresentation $presentation, array $section): void
    {
        $title = (string) ($section['title'] ?? 'Section');
        $headings = is_array($section['headings'] ?? null) ? array_values($section['headings']) : [];
        $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];

        $columnCount = count($headings);
        if ($columnCount === 0 && !empty($rows[0]) && is_array($rows[0])) {
            $columnCount = count(array_values($rows[0]));
            for ($index = 0; $index < $columnCount; $index++) {
                $headings[] = 'Column ' . ($index + 1);
            }
        }

        $columnCount = max(1, $columnCount);
        if (empty($rows)) {
            $slide = $presentation->createSlide();
            $this->stylePptxSectionSlide($slide, $title, 1, 1);

            $emptyShape = $slide->createRichTextShape()
                ->setHeight(420)
                ->setWidth(900)
                ->setOffsetX(30)
                ->setOffsetY(140);
            $emptyShape->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFFFFFFF');

            $emptyRun = $emptyShape->createTextRun('No data available for this section.');
            $emptyRun->getFont()
                ->setSize(21)
                ->setBold(true)
                ->setColor(new Color('FF64748B'));

            return;
        }

        $chunkSize = $columnCount >= 10 ? 8 : ($columnCount >= 7 ? 10 : 12);
        $chunks = array_chunk($rows, $chunkSize);
        $totalChunks = count($chunks);

        foreach ($chunks as $index => $chunk) {
            $slide = $presentation->createSlide();
            $this->stylePptxSectionSlide($slide, $title, $index + 1, $totalChunks);

            $table = $slide->createTableShape($columnCount)
                ->setHeight(390)
                ->setWidth(920)
                ->setOffsetX(20)
                ->setOffsetY(120);

            $columnWidth = max(65, (int) floor(920 / $columnCount));
            $headerFontSize = $columnCount >= 9 ? 8 : 9;
            $bodyFontSize = $columnCount >= 9 ? 7 : 8;

            $headerRow = $table->createRow()->setHeight(26);
            for ($col = 0; $col < $columnCount; $col++) {
                $headerCell = $headerRow->nextCell();
                $headerCell->setWidth($columnWidth);
                $headerCell->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FF0F4C81');
                $headerCell->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $headerRun = $headerCell->createTextRun((string) ($headings[$col] ?? ('Column ' . ($col + 1))));
                $headerRun->getFont()
                    ->setBold(true)
                    ->setSize($headerFontSize)
                    ->setColor(new Color(Color::COLOR_WHITE));
            }

            foreach ($chunk as $rowIndex => $row) {
                $rowData = is_array($row) ? array_values($row) : [$row];
                $bodyRow = $table->createRow()->setHeight(24);

                for ($col = 0; $col < $columnCount; $col++) {
                    $cell = $bodyRow->nextCell();
                    $cell->setWidth($columnWidth);
                    $cell->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB($rowIndex % 2 === 0 ? 'FFFFFFFF' : 'FFF8FBFF');
                    $cell->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    $rawValue = isset($rowData[$col]) ? $rowData[$col] : '';
                    $text = preg_replace('/\s+/', ' ', trim((string) $rawValue));
                    $run = $cell->createTextRun(Str::limit((string) $text, 100, '...'));
                    $run->getFont()
                        ->setSize($bodyFontSize)
                        ->setColor(new Color('FF1F2937'));
                }
            }
        }
    }

    private function stylePptxSectionSlide($slide, string $title, int $part, int $total): void
    {
        $background = $slide->createRichTextShape()
            ->setHeight(720)
            ->setWidth(960)
            ->setOffsetX(0)
            ->setOffsetY(0);
        $background->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFF5F8FC');

        $accent = $slide->createRichTextShape()
            ->setHeight(12)
            ->setWidth(960)
            ->setOffsetX(0)
            ->setOffsetY(0);
        $accent->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF0F4C81');

        $headerTitle = $title;
        if ($total > 1) {
            $headerTitle .= ' (Part ' . $part . '/' . $total . ')';
        }

        $headerBand = $slide->createRichTextShape()
            ->setHeight(54)
            ->setWidth(920)
            ->setOffsetX(20)
            ->setOffsetY(24);
        $headerBand->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF0F4C81');

        $headerText = $slide->createRichTextShape()
            ->setHeight(54)
            ->setWidth(900)
            ->setOffsetX(30)
            ->setOffsetY(33);
        $headerRun = $headerText->createTextRun($headerTitle);
        $headerRun->getFont()
            ->setBold(true)
            ->setSize(21)
            ->setColor(new Color(Color::COLOR_WHITE));
    }

    private function parseTime(string $sendTime): array
    {
        if (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $sendTime) !== 1) {
            return [8, 0];
        }

        [$hour, $minute] = explode(':', $sendTime);

        return [(int) $hour, (int) $minute];
    }

    private function normalizeTriggerType(string $triggerType): string
    {
        $triggerType = strtolower(trim($triggerType));

        if (!in_array($triggerType, ['auto', 'manual'], true)) {
            return 'auto';
        }

        return $triggerType;
    }

    private function resolveScheduleRecipients(ScheduledReport $schedule): array
    {
        $rawRecipients = implode(',', (array) $schedule->recipients);
        $fallbackEmail = (string) optional($schedule->user)->email;

        return $this->parseRecipients($rawRecipients, $fallbackEmail);
    }

    private function recordRun(
        ScheduledReport $schedule,
        string $triggerType,
        string $status,
        int $recipientCount,
        ?string $outputFilename,
        ?string $errorMessage,
        Carbon $ranAt
    ): void {
        ScheduledReportRun::query()->create([
            'scheduled_report_id' => (int) $schedule->id,
            'trigger_type' => $triggerType,
            'status' => $status,
            'scope' => (string) $schedule->scope,
            'format' => (string) $schedule->format,
            'recipient_count' => max(0, $recipientCount),
            'output_filename' => !is_null($outputFilename) && trim($outputFilename) !== '' ? trim($outputFilename) : null,
            'error_message' => !is_null($errorMessage) && trim($errorMessage) !== '' ? trim($errorMessage) : null,
            'ran_at' => $ranAt,
        ]);
    }

    private function makeFilename(string $base, string $extension): string
    {
        $safeBase = Str::slug($base, '-');
        if ($safeBase === '') {
            $safeBase = 'scheduled-report';
        }

        return $safeBase . '-' . now()->format('Ymd-His') . '.' . $extension;
    }
}
