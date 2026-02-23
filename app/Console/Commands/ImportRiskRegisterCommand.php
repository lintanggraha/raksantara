<?php

namespace App\Console\Commands;

use App\RadarGroup;
use App\Risk;
use App\RiskCauseCategory;
use App\RiskCategory;
use App\RiskInherentAnalysis;
use App\RiskResidualAnalysis;
use App\RiskScoreMatrix;
use App\RiskTreatment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportRiskRegisterCommand extends Command
{
    protected $signature = 'risk:import-register
        {file : Path ke file .xls/.xlsx}
        {--year= : Override year import}
        {--sheet=* : Import hanya sheet tertentu (boleh diulang)}
        {--truncate : Hapus data risk sebelum import}
        {--dry-run : Simulasi tanpa insert ke database}';

    protected $description = 'Import risk register dari file Excel RM-02';

    private const DEFAULT_SHEETS = ['M', 'BT', 'DI', 'QA', 'CR', 'PM', 'OM', 'IF', 'CS', 'RI', 'BS', 'HC', 'CO'];
    private const ALLOWED_EXTENSIONS = ['xls', 'xlsx'];

    private array $riskCategoryMap = [];
    private array $riskCauseCategoryMap = [];
    private array $impactScaleMap = [];
    private array $impactScaleList = [];
    private array $impactScaleByCategoryScore = [];
    private array $radarGroupMap = [];
    private array $radarGroupDescriptionMap = [];
    private array $xlsxProbabilityScoreMap = [];
    private array $xlsxImpactScoreMap = [];

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        if (!is_file($file)) {
            $this->error('File tidak ditemukan: ' . $file);

            return self::FAILURE;
        }

        $extension = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $this->error('File harus berformat .xls atau .xlsx');

            return self::FAILURE;
        }

        ini_set('memory_limit', '1024M');

        $availableSheets = $this->listWorksheetNames($file);
        $requestedSheets = $this->option('sheet');
        $sheetNames = !empty($requestedSheets) ? array_values($requestedSheets) : self::DEFAULT_SHEETS;
        $sheetNames = array_values(array_unique($sheetNames));

        $missingSheets = array_values(array_diff($sheetNames, $availableSheets));
        if (!empty($missingSheets)) {
            $this->warn('Sheet tidak ditemukan dan akan di-skip: ' . implode(', ', $missingSheets));
            $sheetNames = array_values(array_diff($sheetNames, $missingSheets));
        }

        if (empty($sheetNames)) {
            $this->error('Tidak ada sheet valid untuk diimport.');

            return self::FAILURE;
        }

        $yearOption = $this->option('year');
        $importYear = !empty($yearOption) ? (int) $yearOption : $this->detectYear($file, $sheetNames[0]);
        if ($importYear < 2000 || $importYear > 2100) {
            $importYear = (int) now()->year;
        }

        $dryRun = (bool) $this->option('dry-run');
        $truncate = (bool) $this->option('truncate');

        $this->prepareLookupMaps();
        $this->prepareWorkbookScoreLookupMaps($file);

        $summary = [
            'sheets' => [],
            'rows_seen' => 0,
            'rows_imported' => 0,
            'rows_skipped' => 0,
            'rows_skipped_missing_title' => 0,
            'rows_skipped_missing_row_no' => 0,
            'treatments_imported' => 0,
        ];

        $runner = function () use ($file, $sheetNames, $importYear, &$summary): void {
            foreach ($sheetNames as $sheetName) {
                $stats = $this->importSheet($file, $sheetName, $importYear);
                $summary['sheets'][] = $stats;
                $summary['rows_seen'] += $stats['rows_seen'];
                $summary['rows_imported'] += $stats['rows_imported'];
                $summary['rows_skipped'] += $stats['rows_skipped'];
                $summary['rows_skipped_missing_title'] += $stats['rows_skipped_missing_title'];
                $summary['rows_skipped_missing_row_no'] += $stats['rows_skipped_missing_row_no'];
                $summary['treatments_imported'] += $stats['treatments_imported'];

                $this->line(sprintf(
                    '[%s] seen=%d imported=%d skipped=%d',
                    $sheetName,
                    $stats['rows_seen'],
                    $stats['rows_imported'],
                    $stats['rows_skipped']
                ));
            }
        };

        try {
            if ($dryRun) {
                DB::beginTransaction();
                $this->truncateDataIfNeeded($truncate);
                $runner();
                DB::rollBack();
            } else {
                DB::transaction(function () use ($truncate, $runner): void {
                    $this->truncateDataIfNeeded($truncate);
                    $runner();
                });
            }
        } catch (\Throwable $e) {
            $this->error('Import gagal: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Import selesai.');
        $this->line('Year: ' . $importYear);
        $this->line('Rows seen: ' . $summary['rows_seen']);
        $this->line('Rows imported: ' . $summary['rows_imported']);
        $this->line('Rows skipped: ' . $summary['rows_skipped']);
        $this->line('Rows skipped (missing title): ' . $summary['rows_skipped_missing_title']);
        $this->line('Rows skipped (missing row no): ' . $summary['rows_skipped_missing_row_no']);
        $this->line('Treatments imported: ' . $summary['treatments_imported']);

        if ($dryRun) {
            $this->warn('Dry-run mode aktif: tidak ada data yang disimpan.');
        }

        return self::SUCCESS;
    }

    private function importSheet(string $file, string $sheetName, int $importYear): array
    {
        $worksheet = $this->loadWorksheet($file, $sheetName, 240, 70);

        $workingUnit = $this->cleanString($this->cell($worksheet, 'E5'));
        $sheetPeriod = $this->cleanString($this->cell($worksheet, 'E4'));
        $sheetYear = $this->extractYearFromText($sheetPeriod);
        $year = $sheetYear ?: $importYear;

        $stats = [
            'sheet' => $sheetName,
            'rows_seen' => 0,
            'rows_imported' => 0,
            'rows_skipped' => 0,
            'rows_skipped_missing_title' => 0,
            'rows_skipped_missing_row_no' => 0,
            'treatments_imported' => 0,
        ];

        $started = false;
        $prevNo = 0;

        for ($row = 9; $row <= 240; $row++) {
            $rowNo = $this->parseRowNumber($this->cell($worksheet, 'B' . $row));

            if (is_null($rowNo)) {
                if ($started) {
                    break;
                }

                $stats['rows_skipped_missing_row_no']++;
                continue;
            }

            if (!$started) {
                if ($rowNo !== 1) {
                    $stats['rows_skipped_missing_row_no']++;
                    continue;
                }
                $started = true;
            } elseif ($rowNo !== ($prevNo + 1)) {
                break;
            }

            $prevNo = $rowNo;
            $stats['rows_seen']++;

            $processName = $this->cleanString($this->cell($worksheet, 'C' . $row));
            $riskIdentification = $this->firstNonEmpty([
                $this->cleanString($this->cell($worksheet, 'E' . $row)),
                $this->cleanString($this->cell($worksheet, 'AT' . $row)),
                $this->cleanString($this->cell($worksheet, 'I' . $row)),
            ]);

            // Mapping XLS -> App:
            // Process Name -> risk_title (Process)
            // Identification -> risk_description (Risk Identification)
            $riskTitle = $this->truncate($this->firstNonEmpty([
                $processName,
                $riskIdentification,
            ]), 191);

            if (empty($riskTitle)) {
                $stats['rows_skipped']++;
                $stats['rows_skipped_missing_title']++;
                continue;
            }

            $riskDescription = $riskIdentification;

            $riskCategoryId = $this->resolveRiskCategoryId($this->cell($worksheet, 'G' . $row));
            $riskCauseCategoryId = $this->resolveRiskCauseCategoryId($this->cell($worksheet, 'H' . $row));

            $riskOwner = $this->firstNonEmpty([
                $this->cleanString($this->cell($worksheet, 'M' . $row)),
                $this->cleanString($this->cell($worksheet, 'AU' . $row)),
                $workingUnit,
            ]) ?: 'N/A';
            $riskOwner = $this->truncate($riskOwner, 191) ?? 'N/A';

            $status = $this->resolveStatus($this->cell($worksheet, 'AW' . $row));

            $inherentProbabilityDescription = $this->cleanString($this->cell($worksheet, 'N' . $row));
            $residualProbabilityDescription = $this->cleanString($this->cell($worksheet, 'Z' . $row));
            $inherentImpactDescription = $this->cleanString($this->cell($worksheet, 'P' . $row));
            $residualImpactDescription = $this->cleanString($this->cell($worksheet, 'AB' . $row));

            [$inherentImpactScaleId, $inherentImpactScoreFromScale] = $this->resolveImpactScale($inherentImpactDescription);
            [$residualImpactScaleId, $residualImpactScoreFromScale] = $this->resolveImpactScale($residualImpactDescription);

            $useStrictXlsxLookup = $this->shouldUseStrictXlsxLookup();

            $inherentProbabilityScore = $this->firstInt([
                $this->lookupProbabilityScoreFromXlsx($inherentProbabilityDescription),
                $this->parseScore($this->cell($worksheet, 'O' . $row)),
                $useStrictXlsxLookup ? null : $this->parseProbabilityScoreFromDescription($inherentProbabilityDescription),
            ]);

            $inherentImpactScore = $this->firstInt([
                $this->lookupImpactScoreFromXlsx($inherentImpactDescription),
                $this->parseScore($this->cell($worksheet, 'Q' . $row)),
                $useStrictXlsxLookup ? null : $inherentImpactScoreFromScale,
            ]);

            $residualProbabilityScore = $this->firstInt([
                $this->lookupProbabilityScoreFromXlsx($residualProbabilityDescription),
                $this->parseScore($this->cell($worksheet, 'AA' . $row)),
                $useStrictXlsxLookup ? null : $this->parseProbabilityScoreFromDescription($residualProbabilityDescription),
            ]);

            $residualImpactScore = $this->firstInt([
                $this->lookupImpactScoreFromXlsx($residualImpactDescription),
                $this->parseScore($this->cell($worksheet, 'AC' . $row)),
                $useStrictXlsxLookup ? null : $residualImpactScoreFromScale,
            ]);

            $inherentRiskScore = $this->calculateRiskScore($inherentProbabilityScore, $inherentImpactScore);
            $residualRiskScore = $this->calculateRiskScore($residualProbabilityScore, $residualImpactScore);

            $riskId = sprintf('%s-%03d', strtoupper($sheetName), $rowNo);

            $risk = Risk::updateOrCreate([
                'year' => $year,
                'risk_id' => $riskId,
            ], [
                'risk_title' => $riskTitle,
                'risk_description' => $riskDescription,
                'risk_category_id' => $riskCategoryId,
                'risk_cause_category_id' => $riskCauseCategoryId,
                'cause' => $this->cleanString($this->cell($worksheet, 'K' . $row)),
                'existing_control' => $this->cleanString($this->cell($worksheet, 'L' . $row)),
                'risk_owner' => $riskOwner,
                'status' => $this->truncate($status, 191) ?? 'Open',
                'probability_notes' => $inherentProbabilityDescription,
                'probability_score' => $inherentProbabilityScore,
                'impact_notes' => $inherentImpactDescription,
                'impact_score' => $inherentImpactScore,
                'impact_scale_id' => $inherentImpactScaleId,
                'risk_score' => $inherentRiskScore,
            ]);

            // Import is idempotent by (year, risk_id): replace treatment rows with latest source data.
            $risk->treatments()->delete();

            $inherentRiskLevel = $this->firstNonEmpty([
                $this->normalizeRiskLevel($this->cell($worksheet, 'S' . $row)),
                $inherentRiskScore ? RiskScoreMatrix::getRiskLevel($inherentRiskScore) : null,
            ]);

            RiskInherentAnalysis::updateOrCreate([
                'risk_id' => $risk->id,
            ], [
                'risk_id' => $risk->id,
                'probability_score' => $inherentProbabilityScore,
                'probability_description' => $inherentProbabilityDescription,
                'impact_scale_id' => $inherentImpactScaleId,
                'impact_score' => $inherentImpactScore,
                'impact_description' => $inherentImpactDescription,
                'impact_amount' => $this->parseUnsignedInt($this->cell($worksheet, 'AL' . $row)),
                'probability_percent' => $this->parseProbabilityPercent($this->cell($worksheet, 'AM' . $row)),
                'potential_loss' => $this->parseUnsignedInt($this->cell($worksheet, 'AN' . $row)),
                'inherent_risk_score' => $inherentRiskScore,
                'risk_level' => $inherentRiskLevel,
            ]);

            $radarDescription = $this->firstNonEmpty([
                $this->cleanString($this->cell($worksheet, 'AJ' . $row)),
                $this->cleanString($this->cell($worksheet, 'I' . $row)),
            ]);
            $radarDescription = $this->truncate($radarDescription, 191);
            $radarCode = $this->deriveRadarCode(
                $this->cleanString($this->cell($worksheet, 'J' . $row)),
                $radarDescription
            );
            $radarCode = $this->truncate($radarCode, 10);
            $radarGroupId = $this->resolveRadarGroupId($radarCode);

            $residualRiskLevel = $this->firstNonEmpty([
                $this->normalizeRiskLevel($this->cell($worksheet, 'AF' . $row)),
                $residualRiskScore ? RiskScoreMatrix::getRiskLevel($residualRiskScore) : null,
            ]);

            $residualAdjustment = $this->parseNullableInt($this->cell($worksheet, 'AE' . $row));
            if (is_null($residualAdjustment)) {
                $rowEffectivenessScore = $this->parseEffectivenessScore(
                    $this->cell($worksheet, 'Y' . $row),
                    $this->cell($worksheet, 'X' . $row)
                );

                if (!is_null($residualProbabilityScore) && !is_null($inherentImpactScore)) {
                    $computedAdjustment = (($residualProbabilityScore - $rowEffectivenessScore) * $inherentImpactScore);
                    if ($computedAdjustment < 1) {
                        $computedAdjustment = 1;
                    }
                    $residualAdjustment = (int) round($computedAdjustment);
                }
            }

            $levelAdjustment = $this->truncate($this->cleanString($this->cell($worksheet, 'AG' . $row)), 191);
            if (
                is_null($levelAdjustment)
                && !is_null($residualAdjustment)
                && !is_null($residualImpactScore)
            ) {
                $levelAdjustment = $this->resolveRiskLevelAdjustmentFromValues(
                    $residualImpactScore,
                    (float) $residualAdjustment
                );
            }

            $adjustmentText = $this->truncate($this->cleanString($this->cell($worksheet, 'AE' . $row)), 191);
            if (is_null($adjustmentText) && !is_null($residualAdjustment)) {
                $adjustmentText = (string) $residualAdjustment;
            }

            RiskResidualAnalysis::updateOrCreate([
                'risk_id' => $risk->id,
            ], [
                'risk_id' => $risk->id,
                'probability_score' => $residualProbabilityScore,
                'probability_description' => $residualProbabilityDescription,
                'impact_scale_id' => $residualImpactScaleId,
                'impact_score' => $residualImpactScore,
                'impact_description' => $residualImpactDescription,
                'impact_amount' => $this->parseUnsignedInt($this->cell($worksheet, 'AO' . $row)),
                'probability_percent' => $this->parseProbabilityPercent($this->cell($worksheet, 'AP' . $row)),
                'potential_loss' => $this->parseUnsignedInt($this->cell($worksheet, 'AQ' . $row)),
                'residual_risk_score' => $residualRiskScore,
                'risk_adjustment' => $residualAdjustment,
                'risk_level' => $residualRiskLevel,
                'risk_level_adjustment' => $levelAdjustment,
                'adjustment' => $adjustmentText,
                'radar_group_id' => $radarGroupId,
                'radar_code' => $radarCode,
                'radar_description' => $radarDescription,
            ]);

            $treatmentOption = $this->cleanString($this->cell($worksheet, 'T' . $row));
            if (!empty($treatmentOption)) {
                RiskTreatment::create([
                    'risk_id' => $risk->id,
                    'option' => $this->truncate($treatmentOption, 191) ?? 'N/A',
                    'description' => $this->cleanString($this->cell($worksheet, 'U' . $row)),
                    'pic' => $this->firstNonEmpty([
                        $this->truncate($this->cleanString($this->cell($worksheet, 'V' . $row)), 191),
                        $riskOwner,
                        'N/A',
                    ]),
                    'deadline' => $this->parseDateOrFallback($this->cell($worksheet, 'W' . $row)),
                    'effectiveness' => $this->firstNonEmpty([
                        $this->truncate($this->cleanString($this->cell($worksheet, 'X' . $row)), 191),
                        'N/A',
                    ]),
                    'effectiveness_score' => $this->parseEffectivenessScore(
                        $this->cell($worksheet, 'Y' . $row),
                        $this->cell($worksheet, 'X' . $row)
                    ),
                ]);
                $stats['treatments_imported']++;
            }

            $stats['rows_imported']++;
        }

        $stats['rows_skipped'] += ($stats['rows_seen'] - $stats['rows_imported']);

        return $stats;
    }

    private function listWorksheetNames(string $file): array
    {
        $reader = $this->createSpreadsheetReader($file);
        $reader->setReadDataOnly(true);
        $infos = $reader->listWorksheetInfo($file);

        return array_map(static function (array $item): string {
            return (string) $item['worksheetName'];
        }, $infos);
    }

    private function loadWorksheet(string $file, string $sheetName, int $maxRow, int $maxColumnIndex): Worksheet
    {
        $reader = $this->createSpreadsheetReader($file);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$sheetName]);
        $reader->setReadFilter(new SheetWindowReadFilter(1, $maxRow, $maxColumnIndex));

        $spreadsheet = $reader->load($file);
        $worksheet = $spreadsheet->getSheetByName($sheetName);

        if (!$worksheet) {
            throw new \RuntimeException('Worksheet tidak bisa dibaca: ' . $sheetName);
        }

        return $worksheet;
    }

    private function createSpreadsheetReader(string $file): IReader
    {
        try {
            return IOFactory::createReaderForFile($file);
        } catch (\Throwable $e) {
            throw new \RuntimeException('File Excel tidak dapat dibaca. Pastikan file .xls/.xlsx valid.', 0, $e);
        }
    }

    private function prepareLookupMaps(): void
    {
        $this->riskCategoryMap = [];
        foreach (RiskCategory::query()->get(['id', 'name']) as $category) {
            $this->riskCategoryMap[$this->normalizeKey($category->name)] = (int) $category->id;
        }

        $this->riskCauseCategoryMap = [];
        foreach (RiskCauseCategory::query()->get(['id', 'name']) as $causeCategory) {
            $this->riskCauseCategoryMap[$this->normalizeKey($causeCategory->name)] = (int) $causeCategory->id;
        }

        $this->impactScaleMap = [];
        $this->impactScaleList = [];
        $this->impactScaleByCategoryScore = [];
        $impactScales = DB::table('impact_scales')->select('id', 'description', 'score')->get();
        foreach ($impactScales as $impactScale) {
            $normalizedKey = $this->normalizeKey((string) $impactScale->description);
            $payload = [
                'id' => (int) $impactScale->id,
                'score' => (int) $impactScale->score,
            ];
            $this->impactScaleMap[$normalizedKey] = $payload;
            $this->impactScaleList[] = [
                'key' => $normalizedKey,
                'id' => (int) $impactScale->id,
                'score' => (int) $impactScale->score,
            ];
        }

        $impactCategoryScales = DB::table('impact_categories as c')
            ->join('impact_scales as s', 's.impact_category_id', '=', 'c.id')
            ->select('c.name as category_name', 's.id', 's.score')
            ->get();
        foreach ($impactCategoryScales as $impactCategoryScale) {
            $categoryKey = $this->normalizeKey((string) $impactCategoryScale->category_name);
            $score = (int) $impactCategoryScale->score;
            $this->impactScaleByCategoryScore[$categoryKey][$score] = (int) $impactCategoryScale->id;
        }

        $this->radarGroupMap = [];
        $this->radarGroupDescriptionMap = [];
        foreach (RadarGroup::query()->get(['id', 'code', 'description']) as $radarGroup) {
            $this->radarGroupMap[$this->normalizeKey($radarGroup->code)] = (int) $radarGroup->id;
            $descKey = $this->normalizeKey($radarGroup->description);
            if ($descKey !== '') {
                $this->radarGroupDescriptionMap[$descKey] = strtoupper((string) $radarGroup->code);
            }
        }
    }

    private function prepareWorkbookScoreLookupMaps(string $file): void
    {
        $this->xlsxProbabilityScoreMap = [];
        $this->xlsxImpactScoreMap = [];

        try {
            $probabilitySheet = $this->loadWorksheet($file, '27', 80, 4);
            for ($row = 18; $row <= 40; $row++) {
                $description = $this->cleanString($this->cell($probabilitySheet, 'C' . $row));
                $score = $this->parseScore($this->cell($probabilitySheet, 'D' . $row));

                if (empty($description) || is_null($score)) {
                    continue;
                }

                $this->xlsxProbabilityScoreMap[$this->normalizeKey($description)] = (int) $score;
            }
        } catch (\Throwable $e) {
            $this->warn('Lookup probability sheet (27) tidak bisa dibaca, fallback parser akan dipakai.');
        }

        try {
            $impactSheet = $this->loadWorksheet($file, '26', 220, 5);
            for ($row = 65; $row <= 200; $row++) {
                $description = $this->cleanString($this->cell($impactSheet, 'D' . $row));
                $score = $this->parseScore($this->cell($impactSheet, 'E' . $row));

                if (empty($description) || is_null($score)) {
                    continue;
                }

                $this->xlsxImpactScoreMap[$this->normalizeKey($description)] = (int) $score;
            }
        } catch (\Throwable $e) {
            $this->warn('Lookup impact sheet (26) tidak bisa dibaca, fallback parser akan dipakai.');
        }
    }

    private function shouldUseStrictXlsxLookup(): bool
    {
        return !empty($this->xlsxProbabilityScoreMap) && !empty($this->xlsxImpactScoreMap);
    }

    private function lookupProbabilityScoreFromXlsx(?string $text): ?int
    {
        if (empty($text)) {
            return null;
        }

        $key = $this->normalizeKey($text);
        if ($key === '') {
            return null;
        }

        return $this->xlsxProbabilityScoreMap[$key] ?? null;
    }

    private function lookupImpactScoreFromXlsx(?string $text): ?int
    {
        if (empty($text)) {
            return null;
        }

        $key = $this->normalizeKey($text);
        if ($key === '') {
            return null;
        }

        return $this->xlsxImpactScoreMap[$key] ?? null;
    }

    private function truncateDataIfNeeded(bool $truncate): void
    {
        if (!$truncate) {
            return;
        }

        RiskTreatment::query()->delete();
        RiskInherentAnalysis::query()->delete();
        RiskResidualAnalysis::query()->delete();
        Risk::query()->delete();
    }

    private function detectYear(string $file, string $firstSheet): int
    {
        $worksheet = $this->loadWorksheet($file, $firstSheet, 10, 10);
        $periodText = $this->cleanString($this->cell($worksheet, 'E4'));
        $year = $this->extractYearFromText($periodText);

        return $year ?: (int) now()->year;
    }

    private function extractYearFromText(?string $text): ?int
    {
        if (empty($text)) {
            return null;
        }

        preg_match_all('/\b(20\d{2})\b/', $text, $matches);
        if (empty($matches[1])) {
            return null;
        }

        $years = array_map('intval', $matches[1]);

        return max($years);
    }

    private function resolveRiskCategoryId($value): ?int
    {
        $key = $this->normalizeKey($this->cleanString($value));

        return $this->riskCategoryMap[$key] ?? null;
    }

    private function resolveRiskCauseCategoryId($value): ?int
    {
        $key = $this->normalizeKey($this->cleanString($value));

        return $this->riskCauseCategoryMap[$key] ?? null;
    }

    private function resolveImpactScale(?string $impactDescription): array
    {
        if (empty($impactDescription)) {
            return [null, null];
        }

        $key = $this->normalizeKey($impactDescription);
        $matched = $this->impactScaleMap[$key] ?? null;
        if (!$matched) {
            $bestCandidate = null;
            $bestSimilarity = 0.0;

            foreach ($this->impactScaleList as $candidate) {
                if ($candidate['key'] === '') {
                    continue;
                }

                similar_text($key, $candidate['key'], $similarity);
                if ($similarity > $bestSimilarity) {
                    $bestSimilarity = $similarity;
                    $bestCandidate = $candidate;
                }
            }

            if ($bestSimilarity < 55.0 || is_null($bestCandidate)) {
                [$categoryKey, $score] = $this->inferImpactCategoryAndScore($impactDescription);
                if (is_null($score)) {
                    return [null, null];
                }

                if (!is_null($categoryKey) && isset($this->impactScaleByCategoryScore[$categoryKey][$score])) {
                    return [$this->impactScaleByCategoryScore[$categoryKey][$score], $score];
                }

                return [null, $score];
            }

            return [$bestCandidate['id'], $bestCandidate['score']];
        }

        return [$matched['id'], $matched['score']];
    }

    private function inferImpactCategoryAndScore(string $impactDescription): array
    {
        $text = strtolower($impactDescription);
        $normalized = $this->normalizeKey($impactDescription);

        $categoryKey = null;

        if (strpos($normalized, 'financialsiber') !== false) {
            $categoryKey = $this->normalizeKey('Financial Siber');
            if (strpos($normalized, '24') !== false && strpos($normalized, '30') !== false) {
                return [$categoryKey, 5];
            }
            if (strpos($normalized, '18') !== false && strpos($normalized, '24') !== false) {
                return [$categoryKey, 4];
            }
            if (strpos($normalized, '12') !== false && strpos($normalized, '18') !== false) {
                return [$categoryKey, 3];
            }
            if (strpos($normalized, '6') !== false && strpos($normalized, '12') !== false) {
                return [$categoryKey, 2];
            }
            if (strpos($normalized, '0') !== false && strpos($normalized, '6') !== false) {
                return [$categoryKey, 1];
            }
        }

        if (strpos($normalized, 'pencapaiansla') !== false || strpos($normalized, 'sla') !== false) {
            $categoryKey = $this->normalizeKey('SLA');
            if (preg_match('/<\s*90/', $text) === 1) {
                return [$categoryKey, 5];
            }
            if (preg_match('/90[^0-9]*s\\.d[^0-9]*<=?\\s*92/', $text) === 1 || strpos($normalized, '9092') !== false) {
                return [$categoryKey, 4];
            }
            if (preg_match('/92[^0-9]*s\\.d[^0-9]*<=?\\s*94/', $text) === 1 || strpos($normalized, '9294') !== false) {
                return [$categoryKey, 3];
            }
            if (preg_match('/94[^0-9]*s\\.d[^0-9]*<=?\\s*95/', $text) === 1 || strpos($normalized, '9495') !== false) {
                return [$categoryKey, 2];
            }
            if (preg_match('/>\s*95/', $text) === 1) {
                return [$categoryKey, 1];
            }
        }

        if (strpos($normalized, 'penyelesaianproyek') !== false || strpos($normalized, 'proyek') !== false) {
            $categoryKey = $this->normalizeKey('Penyelesaian Proyek');
            if (preg_match('/<\s*0\\.68/', $text) === 1) {
                return [$categoryKey, 5];
            }
            if (preg_match('/0\\.68[^0-9]*s\\.d[^0-9]*<=?\\s*0\\.73/', $text) === 1 || strpos($normalized, '068073') !== false) {
                return [$categoryKey, 4];
            }
            if (preg_match('/0\\.73[^0-9]*s\\.d[^0-9]*<=?\\s*0\\.78/', $text) === 1 || strpos($normalized, '073078') !== false) {
                return [$categoryKey, 3];
            }
            if (preg_match('/0\\.78[^0-9]*s\\.d[^0-9]*<=?\\s*0\\.83/', $text) === 1 || strpos($normalized, '078083') !== false) {
                return [$categoryKey, 2];
            }
            if (preg_match('/>\s*0\\.83/', $text) === 1) {
                return [$categoryKey, 1];
            }
        }

        if (strpos($normalized, 'keamananinformasi') !== false) {
            $categoryKey = $this->normalizeKey('Keamanan Informasi');
            if (strpos($normalized, '30hari') !== false) {
                return [$categoryKey, 5];
            }
            if (strpos($normalized, '180hari') !== false) {
                return [$categoryKey, 4];
            }
            if (strpos($normalized, 'minor') !== false || strpos($normalized, 'kesalahanmanusia') !== false) {
                return [$categoryKey, 3];
            }
        }

        if (strpos($normalized, 'prosedurinternal') !== false) {
            $categoryKey = $this->normalizeKey('Kepatuhan');
            if (strpos($normalized, '30hari') !== false) {
                return [$categoryKey, 5];
            }
            if (strpos($normalized, '180hari') !== false) {
                return [$categoryKey, 4];
            }
            if (strpos($normalized, 'minor') !== false) {
                return [$categoryKey, 2];
            }

            return [$categoryKey, 3];
        }

        if (strpos($normalized, 'keselamatankerja') !== false) {
            $categoryKey = $this->normalizeKey('Keselamatan Kerja');
            if (strpos($normalized, 'kematian') !== false || strpos($normalized, 'cacat') !== false) {
                return [$categoryKey, 5];
            }
            if (strpos($normalized, 'cederasedang') !== false) {
                return [$categoryKey, 4];
            }
            if (strpos($normalized, 'cederaringan') !== false) {
                return [$categoryKey, 3];
            }
            if (strpos($normalized, 'kerugiankecil') !== false) {
                return [$categoryKey, 2];
            }

            return [$categoryKey, 1];
        }

        return [null, null];
    }

    private function resolveRadarGroupId(?string $radarCode): ?int
    {
        if (empty($radarCode)) {
            return null;
        }

        return $this->radarGroupMap[$this->normalizeKey($radarCode)] ?? null;
    }

    private function deriveRadarCode(?string $rawCode, ?string $radarDescription): ?string
    {
        $code = strtoupper(trim((string) $rawCode));
        if ($code !== '') {
            // Normalisasi ringan untuk input seperti "O 1" -> "O1"
            $code = preg_replace('/\s+/', '', $code);

            return $code;
        }

        $description = trim((string) $radarDescription);
        if ($description === '') {
            return null;
        }

        if (preg_match('/\b([SOCF]\d{1,2})\b/i', $description, $matches) === 1) {
            return strtoupper($matches[1]);
        }

        $normalizedDescription = $this->normalizeKey($description);

        if ($normalizedDescription === '' || in_array($normalizedDescription, ['none', 'na', 'n'], true)) {
            return 'N/A';
        }

        // Fallback: cari code berdasarkan kemiripan deskripsi radar group
        $bestCode = null;
        $bestSimilarity = 0.0;
        foreach ($this->radarGroupDescriptionMap as $groupKey => $groupCode) {
            if ($groupKey === '') {
                continue;
            }

            similar_text($normalizedDescription, $groupKey, $similarity);
            if ($similarity > $bestSimilarity) {
                $bestSimilarity = $similarity;
                $bestCode = (string) $groupCode;
            }
        }

        if ($bestSimilarity >= 65.0 && !empty($bestCode)) {
            return strtoupper(trim($bestCode));
        }

        return null;
    }

    private function calculateRiskScore(?int $probabilityScore, ?int $impactScore): ?int
    {
        if (is_null($probabilityScore) || is_null($impactScore)) {
            return null;
        }

        $score = RiskScoreMatrix::getScore($probabilityScore, $impactScore);

        return is_null($score) ? ($probabilityScore * $impactScore) : (int) $score;
    }

    private function parseScore($value): ?int
    {
        $number = $this->parseNumeric($value);
        if (is_null($number)) {
            return null;
        }

        $score = (int) round($number);
        if ($score < 1 || $score > 5) {
            return null;
        }

        return $score;
    }

    private function parseProbabilityScoreFromDescription(?string $text): ?int
    {
        if (empty($text)) {
            return null;
        }

        $lowerText = strtolower($text);
        if (preg_match('/90\s*-\s*100\s*%/', $lowerText) === 1 || strpos($lowerText, 'hampir pasti') !== false) {
            return 5;
        }

        if (preg_match('/70\s*-\s*80\s*%/', $lowerText) === 1 || strpos($lowerText, 'sangat mungkin') !== false) {
            return 4;
        }

        if (preg_match('/50\s*-\s*60\s*%/', $lowerText) === 1 || strpos($lowerText, 'mungkin terjadi') !== false) {
            return 3;
        }

        if (preg_match('/30\s*-\s*40\s*%/', $lowerText) === 1 || strpos($lowerText, 'tidak mungkin terjadi') !== false) {
            return 2;
        }

        if (preg_match('/10\s*-\s*20\s*%/', $lowerText) === 1 || strpos($lowerText, 'sangat tidak mungkin') !== false) {
            return 1;
        }

        return null;
    }

    private function parseProbabilityPercent($value): ?float
    {
        $number = $this->parseNumeric($value);
        if (is_null($number)) {
            return null;
        }

        if ($number > 1) {
            $number = $number / 100;
        }

        $number = max(0.0, min(1.0, (float) $number));

        return round($number, 4);
    }

    private function parseUnsignedInt($value): ?int
    {
        $number = $this->parseNumeric($value);
        if (is_null($number)) {
            return null;
        }

        if ($number < 0) {
            return null;
        }

        return (int) round($number);
    }

    private function parseNullableInt($value): ?int
    {
        $number = $this->parseNumeric($value);
        if (is_null($number)) {
            return null;
        }

        return (int) round($number);
    }

    private function parseEffectivenessScore($value, $effectivenessLabel): float
    {
        $numeric = $this->parseNumeric($value);
        if (!is_null($numeric)) {
            return (float) round(max(0.0, min(1.0, $numeric)), 2);
        }

        $label = strtolower($this->cleanString($effectivenessLabel));
        if (strpos($label, 'high') !== false || strpos($label, 'efektif') !== false) {
            return 1.00;
        }

        if (strpos($label, 'medium') !== false || strpos($label, 'cukup') !== false) {
            return 0.50;
        }

        if (strpos($label, 'low') !== false || strpos($label, 'kurang') !== false) {
            return 0.25;
        }

        return 0.00;
    }

    private function parseDateOrFallback($value): string
    {
        $text = $this->cleanString($value);
        if ($this->isSpreadsheetError($text)) {
            return now()->toDateString();
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return now()->toDateString();
            }
        }

        if (!empty($text)) {
            try {
                return Carbon::parse($text)->toDateString();
            } catch (\Throwable $e) {
                return now()->toDateString();
            }
        }

        return now()->toDateString();
    }

    private function parseRowNumber($value): ?int
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $number = (int) round((float) $value);

            return $number > 0 ? $number : null;
        }

        $text = $this->cleanString($value);
        if ($text === '' || $this->isSpreadsheetError($text)) {
            return null;
        }

        if (preg_match('/^\d+$/', $text) !== 1) {
            return null;
        }

        $number = (int) $text;

        return $number > 0 ? $number : null;
    }

    private function parseNumeric($value): ?float
    {
        if (is_null($value)) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $text = $this->cleanString($value);
        if ($text === '' || $this->isSpreadsheetError($text)) {
            return null;
        }

        $text = str_replace(['Rp', 'rp', '%'], '', $text);
        $text = preg_replace('/[^0-9,.\-]/', '', $text);
        if ($text === '' || $text === '-' || $text === '--') {
            return null;
        }

        $hasComma = strpos($text, ',') !== false;
        $hasDot = strpos($text, '.') !== false;

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($text, ',');
            $lastDot = strrpos($text, '.');
            if ($lastComma > $lastDot) {
                $text = str_replace('.', '', $text);
                $text = str_replace(',', '.', $text);
            } else {
                $text = str_replace(',', '', $text);
            }
        } elseif ($hasComma && !$hasDot) {
            if (preg_match('/,\d{1,2}$/', $text) === 1) {
                $text = str_replace(',', '.', $text);
            } else {
                $text = str_replace(',', '', $text);
            }
        }

        return is_numeric($text) ? (float) $text : null;
    }

    private function resolveStatus($value): string
    {
        $text = strtolower($this->cleanString($value));
        if (strpos($text, 'selesai') !== false || strpos($text, 'close') !== false || strpos($text, 'closed') !== false) {
            return 'Closed';
        }

        if (strpos($text, 'monitor') !== false) {
            return 'Monitoring';
        }

        return 'Open';
    }

    private function normalizeRiskLevel($value): ?string
    {
        $text = strtolower($this->cleanString($value));
        if ($text === '') {
            return null;
        }

        if (in_array($text, ['low', 'rendah'], true)) {
            return 'Low';
        }

        if (in_array($text, ['low to moderate', 'low-moderate', 'moderat rendah'], true)) {
            return 'Low to Moderate';
        }

        if (in_array($text, ['moderate', 'moderat'], true)) {
            return 'Moderate';
        }

        if (in_array($text, ['moderate to high', 'moderat tinggi'], true)) {
            return 'Moderate to High';
        }

        if (in_array($text, ['high', 'tinggi'], true)) {
            return 'High';
        }

        return null;
    }

    private function resolveRiskLevelAdjustmentFromValues(int $residualImpactScore, float $adjustment): string
    {
        if ($residualImpactScore < 5 && $adjustment < 6) {
            return 'Rendah';
        }

        if ($residualImpactScore === 5 && $adjustment >= 10) {
            return 'Tinggi';
        }

        if ($residualImpactScore < 5 && $adjustment >= 15) {
            return 'Tinggi';
        }

        return 'Moderat';
    }

    private function buildRiskDescription(?string $processName, ?string $target, ?string $keyIndicator): ?string
    {
        $parts = [];

        if (!empty($processName)) {
            $parts[] = 'Process: ' . $processName;
        }

        if (!empty($target)) {
            $parts[] = 'Target: ' . $target;
        }

        if (!empty($keyIndicator)) {
            $parts[] = 'Key Indicator: ' . $keyIndicator;
        }

        if (empty($parts)) {
            return null;
        }

        return implode(PHP_EOL, $parts);
    }

    private function cell(Worksheet $worksheet, string $coordinate)
    {
        return $worksheet->getCell($coordinate)->getValue();
    }

    private function cleanString($value): string
    {
        if (is_null($value)) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        // Formula tanpa cached value dari workbook dibaca sebagai string "=...".
        if (strpos($text, '=') === 0) {
            return '';
        }

        $text = preg_replace('/\s+/u', ' ', $text);

        return trim((string) $text);
    }

    private function truncate(?string $value, int $max): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $text = $this->cleanString($value);
        if ($text === '') {
            return null;
        }

        if (function_exists('mb_strcut')) {
            return mb_strcut($text, 0, $max, 'UTF-8');
        }

        return substr($text, 0, $max);
    }

    private function normalizeKey(?string $value): string
    {
        $cleaned = strtolower($this->cleanString($value));
        if ($cleaned === '') {
            return '';
        }

        return preg_replace('/[^a-z0-9]+/', '', $cleaned);
    }

    private function isSpreadsheetError(?string $value): bool
    {
        if (is_null($value)) {
            return false;
        }

        return in_array($value, ['#N/A', '#REF!', '#DIV/0!', '#VALUE!', '#NAME?', '#NUM!', '#NULL!'], true);
    }

    private function firstNonEmpty(array $values): ?string
    {
        foreach ($values as $value) {
            $text = $this->cleanString($value);
            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }

    private function firstInt(array $values): ?int
    {
        foreach ($values as $value) {
            if (!is_null($value)) {
                return (int) $value;
            }
        }

        return null;
    }
}

class SheetWindowReadFilter implements IReadFilter
{
    private int $startRow;
    private int $endRow;
    private int $maxColumnIndex;

    public function __construct(int $startRow, int $endRow, int $maxColumnIndex)
    {
        $this->startRow = $startRow;
        $this->endRow = $endRow;
        $this->maxColumnIndex = $maxColumnIndex;
    }

    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        if ($row < $this->startRow || $row > $this->endRow) {
            return false;
        }

        return Coordinate::columnIndexFromString($columnAddress) <= $this->maxColumnIndex;
    }
}
