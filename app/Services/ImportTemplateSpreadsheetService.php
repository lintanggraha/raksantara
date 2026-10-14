<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportTemplateSpreadsheetService
{
    private const CONTENT_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /**
     * @return array{filename: string, spreadsheet: Spreadsheet}
     */
    public function build(string $template): array
    {
        return match ($template) {
            'risk-register' => $this->buildRiskRegisterTemplate(),
            'audit-dashboard' => $this->buildAuditDashboardTemplate(),
            'ojk-mitigation' => $this->buildOjkMitigationTemplate(),
            default => throw new \InvalidArgumentException('Unknown import template.'),
        };
    }

    public function contentType(): string
    {
        return self::CONTENT_TYPE;
    }

    /**
     * @return array{filename: string, spreadsheet: Spreadsheet}
     */
    private function buildRiskRegisterTemplate(): array
    {
        $year = (int) now()->year;
        $spreadsheet = $this->newSpreadsheet(
            'Template Import Risk Register',
            'Contoh workbook RM-02 untuk import risk register.'
        );

        $readme = $spreadsheet->getActiveSheet();
        $readme->setTitle('README');
        $this->populateRiskReadmeSheet($readme, $year);

        $referenceSheet = new Worksheet($spreadsheet, 'REFERENSI');
        $spreadsheet->addSheet($referenceSheet);
        $this->populateRiskReferenceSheet($referenceSheet);

        $riskCategories = $this->loadRiskCategories();
        $causeCategories = $this->loadRiskCauseCategories();
        $radarGroups = $this->loadRadarGroups();
        $impactScales = $this->loadImpactScales();

        $sheetUnits = [
            'BT' => 'Business Technology',
            'DI' => 'Digital Integration',
            'CR' => 'Corporate Risk',
            'OM' => 'Operation Management',
            'CS' => 'Cyber Security',
            'BS' => 'Business Support',
            'HC' => 'Human Capital',
            'SUM' => 'Summary Unit',
        ];

        foreach ($sheetUnits as $sheetName => $unitName) {
            $sheet = new Worksheet($spreadsheet, $sheetName);
            $spreadsheet->addSheet($sheet);
            $this->populateRiskInputSheet(
                $sheet,
                $sheetName,
                $unitName,
                $year,
                $sheetName === 'BT',
                $riskCategories,
                $causeCategories,
                $radarGroups,
                $impactScales
            );
        }

        $impactLookupSheet = new Worksheet($spreadsheet, '26');
        $spreadsheet->addSheet($impactLookupSheet);
        $this->populateImpactLookupSheet($impactLookupSheet, $impactScales);
        $impactLookupSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $probabilityLookupSheet = new Worksheet($spreadsheet, '27');
        $spreadsheet->addSheet($probabilityLookupSheet);
        $this->populateProbabilityLookupSheet($probabilityLookupSheet);
        $probabilityLookupSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $spreadsheet->setActiveSheetIndex(0);

        return [
            'filename' => 'risk-register-import-template.xlsx',
            'spreadsheet' => $spreadsheet,
        ];
    }

    /**
     * @return array{filename: string, spreadsheet: Spreadsheet}
     */
    private function buildAuditDashboardTemplate(): array
    {
        $year = (int) now()->year;
        $spreadsheet = $this->newSpreadsheet(
            'Template Import Audit Dashboard',
            'Contoh workbook import audit monitoring.'
        );

        $readme = $spreadsheet->getActiveSheet();
        $readme->setTitle('README');
        $this->populateAuditReadmeSheet($readme, $year);

        $sheet = new Worksheet($spreadsheet, 'SUMMARY UPDATE');
        $spreadsheet->addSheet($sheet);
        $this->populateAuditTemplateSheet($sheet, $year);
        $spreadsheet->setActiveSheetIndex(0);

        return [
            'filename' => 'audit-dashboard-import-template.xlsx',
            'spreadsheet' => $spreadsheet,
        ];
    }

    /**
     * @return array{filename: string, spreadsheet: Spreadsheet}
     */
    private function buildOjkMitigationTemplate(): array
    {
        $year = (int) now()->year;
        $spreadsheet = $this->newSpreadsheet(
            'Template Import Mitigasi OJK',
            'Contoh workbook import monitoring mitigasi OJK.'
        );

        $readme = $spreadsheet->getActiveSheet();
        $readme->setTitle('README');
        $this->populateOjkReadmeSheet($readme, $year);

        $sheet = new Worksheet($spreadsheet, 'Sheet1');
        $spreadsheet->addSheet($sheet);
        $this->populateOjkTemplateSheet($sheet, $year);
        $spreadsheet->setActiveSheetIndex(0);

        return [
            'filename' => 'ojk-mitigation-import-template.xlsx',
            'spreadsheet' => $spreadsheet,
        ];
    }

    private function newSpreadsheet(string $title, string $description): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator((string) config('app.name', 'Risk Management System'))
            ->setTitle($title)
            ->setDescription($description);

        return $spreadsheet;
    }

    private function populateRiskReadmeSheet(Worksheet $sheet, int $year): void
    {
        $sheet->setCellValue('A1', 'Template Import Risk Register (RM-02)');
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A3', 'Cara pakai');
        $sheet->setCellValue('A4', '1. Isi data pada sheet BT, DI, CR, OM, CS, BS, HC, atau SUM.');
        $sheet->setCellValue('A5', '2. Mulai input data di baris 9. Baris 8 sudah disiapkan sebagai panduan kolom.');
        $sheet->setCellValue('A6', '3. Cell E4 harus berisi periode/tahun, dan E5 berisi nama unit kerja.');
        $sheet->setCellValue('A7', '4. Sheet 26 dan 27 disediakan sebagai lookup score untuk impact dan probability.');
        $sheet->setCellValue('A8', '5. Hapus atau timpa baris contoh pada sheet BT sebelum upload jika tidak dipakai.');
        $sheet->setCellValue('A10', 'Kolom penting');
        $sheet->setCellValue('A11', 'B: nomor urut risiko');
        $sheet->setCellValue('A12', 'C: process / risk title');
        $sheet->setCellValue('A13', 'D: sasaran target');
        $sheet->setCellValue('A14', 'E: risk identification');
        $sheet->setCellValue('A15', 'G-H: kategori risiko dan cause category');
        $sheet->setCellValue('A16', 'M: risk owner');
        $sheet->setCellValue('A17', 'N-O-P-Q: inherent probability/impact description + score');
        $sheet->setCellValue('A18', 'T-U-V-W-X-Y: treatment option, description, PIC, deadline, effectiveness, score');
        $sheet->setCellValue('A19', 'Z-AA-AB-AC: residual probability/impact description + score');
        $sheet->setCellValue('A20', 'AW: status (Open / Monitoring / Closed)');
        $sheet->setCellValue('D3', 'Tahun contoh');
        $sheet->setCellValue('D4', $year);

        $this->styleTitle($sheet, 'A1:F1');
        $this->styleSectionHeader($sheet, 'A3:B3');
        $this->styleSectionHeader($sheet, 'A10:B10');
        $sheet->getStyle('A4:A20')->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('A')->setWidth(78);
        $sheet->getColumnDimension('D')->setWidth(16);
    }

    private function populateRiskReferenceSheet(Worksheet $sheet): void
    {
        $riskCategories = $this->loadRiskCategories();
        $causeCategories = $this->loadRiskCauseCategories();
        $radarGroups = $this->loadRadarGroups();
        $impactScales = $this->loadImpactScales();

        $sheet->setCellValue('A1', 'Referensi Value');
        $sheet->mergeCells('A1:N1');
        $this->styleTitle($sheet, 'A1:N1');

        $sheet->setCellValue('A3', 'Risk Category');
        $sheet->setCellValue('D3', 'Cause Category');
        $sheet->setCellValue('G3', 'Radar Code');
        $sheet->setCellValue('H3', 'Radar Description');
        $sheet->setCellValue('J3', 'Impact Description');
        $sheet->setCellValue('K3', 'Score');
        $sheet->setCellValue('M3', 'Probability Description');
        $sheet->setCellValue('N3', 'Score');
        $this->styleColumnHeader($sheet, 'A3:A3');
        $this->styleColumnHeader($sheet, 'D3:D3');
        $this->styleColumnHeader($sheet, 'G3:H3');
        $this->styleColumnHeader($sheet, 'J3:K3');
        $this->styleColumnHeader($sheet, 'M3:N3');

        $row = 4;
        foreach ($riskCategories as $value) {
            $sheet->setCellValue('A' . $row, $value);
            $row++;
        }

        $row = 4;
        foreach ($causeCategories as $value) {
            $sheet->setCellValue('D' . $row, $value);
            $row++;
        }

        $row = 4;
        foreach ($radarGroups as $group) {
            $sheet->setCellValue('G' . $row, $group['code']);
            $sheet->setCellValue('H' . $row, $group['description']);
            $row++;
        }

        $row = 4;
        foreach ($impactScales as $impactScale) {
            $sheet->setCellValue('J' . $row, $impactScale['description']);
            $sheet->setCellValue('K' . $row, $impactScale['score']);
            $row++;
        }

        $row = 4;
        foreach ($this->probabilityLookupRows() as $probabilityRow) {
            $sheet->setCellValue('M' . $row, $probabilityRow['description']);
            $sheet->setCellValue('N' . $row, $probabilityRow['score']);
            $row++;
        }

        $this->setColumnWidths($sheet, [
            'A' => 28,
            'D' => 22,
            'G' => 12,
            'H' => 46,
            'J' => 46,
            'K' => 10,
            'M' => 36,
            'N' => 10,
        ]);
    }

    /**
     * @param  array<int, string>  $riskCategories
     * @param  array<int, string>  $causeCategories
     * @param  array<int, array{code: string, description: string}>  $radarGroups
     * @param  array<int, array{description: string, score: int}>  $impactScales
     */
    private function populateRiskInputSheet(
        Worksheet $sheet,
        string $sheetName,
        string $unitName,
        int $year,
        bool $withExampleRow,
        array $riskCategories,
        array $causeCategories,
        array $radarGroups,
        array $impactScales
    ): void {
        $riskCategory = $this->pickPreferredValue($riskCategories, ['Operasional', 'Kepatuhan', 'Teknis']);
        $causeCategory = $this->pickPreferredValue($causeCategories, ['Process', 'People', 'Product']);
        $inherentImpact = $this->pickPreferredImpact($impactScales, ['Gangguan sedang', 'Pelanggaran sedang']);
        $residualImpact = $this->pickPreferredImpact($impactScales, ['Gangguan kecil', 'Pelanggaran minor']);
        $radarGroup = $this->pickPreferredRadarGroup($radarGroups, ['O5', 'O4', 'O2']);

        $sheet->setCellValue('A1', 'Template Input RM-02 - ' . $sheetName);
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A2', 'Isi data mulai baris 9. Kolom yang tidak dipakai boleh dikosongkan.');
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A7', $withExampleRow
            ? 'Baris 9 berisi contoh. Hapus atau timpa sebelum import.'
            : 'Mulai isi data baru dari baris 9.');
        $sheet->mergeCells('A7:H7');
        $sheet->setCellValue('E4', 'Periode Risk Register ' . $year);
        $sheet->setCellValue('E5', $unitName);

        $headers = [
            'B8' => 'No',
            'C8' => 'Process Name',
            'D8' => 'Sasaran Target',
            'E8' => 'Risk Identification',
            'G8' => 'Risk Category',
            'H8' => 'Cause Category',
            'J8' => 'Radar Code',
            'K8' => 'Cause',
            'L8' => 'Existing Control',
            'M8' => 'Risk Owner',
            'N8' => 'Inherent Probability Description',
            'O8' => 'Inherent Probability Score',
            'P8' => 'Inherent Impact Description',
            'Q8' => 'Inherent Impact Score',
            'S8' => 'Inherent Risk Level',
            'T8' => 'Treatment Option',
            'U8' => 'Treatment Description',
            'V8' => 'Treatment PIC',
            'W8' => 'Deadline',
            'X8' => 'Effectiveness',
            'Y8' => 'Effectiveness Score',
            'Z8' => 'Residual Probability Description',
            'AA8' => 'Residual Probability Score',
            'AB8' => 'Residual Impact Description',
            'AC8' => 'Residual Impact Score',
            'AE8' => 'Risk Adjustment',
            'AF8' => 'Residual Risk Level',
            'AG8' => 'Level Adjustment',
            'AJ8' => 'Radar Description',
            'AL8' => 'Inherent Impact Amount',
            'AM8' => 'Inherent Probability %',
            'AN8' => 'Inherent Potential Loss',
            'AO8' => 'Residual Impact Amount',
            'AP8' => 'Residual Probability %',
            'AQ8' => 'Residual Potential Loss',
            'AW8' => 'Status',
        ];

        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        $this->styleTitle($sheet, 'A1:F1');
        $sheet->getStyle('A2:H2')->getFont()->setItalic(true);
        $sheet->getStyle('A7:H7')->getFont()->setBold(true);
        $this->styleColumnHeader($sheet, 'B8:AW8', '0F766E');
        $sheet->getStyle('B8:AW8')->getAlignment()->setWrapText(true);

        if ($withExampleRow) {
            $sheet->setCellValue('B9', 1);
            $sheet->setCellValue('C9', 'Monitoring transaksi virtual account');
            $sheet->setCellValue('D9', 'Menjaga SLA monitoring fraud >= 95%');
            $sheet->setCellValue('E9', 'Keterlambatan deteksi anomali transaksi pada channel VA');
            $sheet->setCellValue('G9', $riskCategory);
            $sheet->setCellValue('H9', $causeCategory);
            $sheet->setCellValue('J9', $radarGroup['code']);
            $sheet->setCellValue('K9', 'Kapasitas monitoring manual belum menutup jam sibuk.');
            $sheet->setCellValue('L9', 'Dashboard monitoring harian dan alert email.');
            $sheet->setCellValue('M9', $unitName);
            $sheet->setCellValue('N9', 'Mungkin Terjadi (50 - 60%)');
            $sheet->setCellValue('O9', 3);
            $sheet->setCellValue('P9', $inherentImpact);
            $sheet->setCellValue('Q9', 3);
            $sheet->setCellValue('S9', 'Moderate');
            $sheet->setCellValue('T9', 'Mitigate');
            $sheet->setCellValue('U9', 'Aktivasi alert real-time dan review rule mingguan.');
            $sheet->setCellValue('V9', $unitName);
            $sheet->setCellValue('W9', '30/06/' . $year);
            $sheet->setCellValue('X9', 'Cukup Efektif');
            $sheet->setCellValue('Y9', 0.5);
            $sheet->setCellValue('Z9', 'Tidak Mungkin Terjadi (30 - 40%)');
            $sheet->setCellValue('AA9', 2);
            $sheet->setCellValue('AB9', $residualImpact);
            $sheet->setCellValue('AC9', 2);
            $sheet->setCellValue('AE9', 4);
            $sheet->setCellValue('AF9', 'Low to Moderate');
            $sheet->setCellValue('AG9', 'Rendah');
            $sheet->setCellValue('AJ9', $radarGroup['description']);
            $sheet->setCellValue('AL9', 250000000);
            $sheet->setCellValue('AM9', 55);
            $sheet->setCellValue('AN9', 137500000);
            $sheet->setCellValue('AO9', 100000000);
            $sheet->setCellValue('AP9', 35);
            $sheet->setCellValue('AQ9', 35000000);
            $sheet->setCellValue('AW9', 'Monitoring');
            $sheet->getStyle('B9:AW9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFDE68A');
        }

        $sheet->freezePane('B9');
        $sheet->setAutoFilter('B8:AW8');
        $sheet->getRowDimension(8)->setRowHeight(34);
        $sheet->getStyle('B9:AW20')->getAlignment()->setWrapText(true);
        $sheet->getStyle('AL9:AQ20')->getNumberFormat()->setFormatCode('#,##0');
        $this->setColumnWidths($sheet, [
            'A' => 6,
            'B' => 8,
            'C' => 26,
            'D' => 26,
            'E' => 36,
            'G' => 20,
            'H' => 18,
            'J' => 12,
            'K' => 28,
            'L' => 28,
            'M' => 20,
            'N' => 30,
            'O' => 12,
            'P' => 30,
            'Q' => 12,
            'S' => 18,
            'T' => 16,
            'U' => 32,
            'V' => 20,
            'W' => 16,
            'X' => 18,
            'Y' => 16,
            'Z' => 30,
            'AA' => 12,
            'AB' => 30,
            'AC' => 12,
            'AE' => 16,
            'AF' => 18,
            'AG' => 18,
            'AJ' => 40,
            'AL' => 18,
            'AM' => 16,
            'AN' => 20,
            'AO' => 18,
            'AP' => 16,
            'AQ' => 20,
            'AW' => 14,
        ]);
    }

    /**
     * @param  array<int, array{description: string, score: int}>  $impactScales
     */
    private function populateImpactLookupSheet(Worksheet $sheet, array $impactScales): void
    {
        $sheet->setCellValue('C64', 'Impact Description');
        $sheet->setCellValue('D64', 'Impact Description');
        $sheet->setCellValue('E64', 'Score');
        $this->styleColumnHeader($sheet, 'C64:E64');

        $row = 65;
        foreach ($impactScales as $impactScale) {
            $sheet->setCellValue('D' . $row, $impactScale['description']);
            $sheet->setCellValue('E' . $row, $impactScale['score']);
            $row++;
        }

        $this->setColumnWidths($sheet, [
            'C' => 8,
            'D' => 48,
            'E' => 10,
        ]);
    }

    private function populateProbabilityLookupSheet(Worksheet $sheet): void
    {
        $sheet->setCellValue('B17', 'Probability Description');
        $sheet->setCellValue('C17', 'Probability Description');
        $sheet->setCellValue('D17', 'Score');
        $this->styleColumnHeader($sheet, 'B17:D17');

        $row = 18;
        foreach ($this->probabilityLookupRows() as $lookupRow) {
            $sheet->setCellValue('C' . $row, $lookupRow['description']);
            $sheet->setCellValue('D' . $row, $lookupRow['score']);
            $row++;
        }

        $this->setColumnWidths($sheet, [
            'B' => 8,
            'C' => 36,
            'D' => 10,
        ]);
    }

    private function populateAuditReadmeSheet(Worksheet $sheet, int $year): void
    {
        $sheet->setCellValue('A1', 'Template Import Audit Dashboard');
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A3', 'Cara pakai');
        $sheet->setCellValue('A4', '1. Isi data pada sheet SUMMARY UPDATE.');
        $sheet->setCellValue('A5', '2. Header contoh ada di baris 2 dan bisa langsung dipakai.');
        $sheet->setCellValue('A6', '3. Minimal pastikan ada kolom Bank, Audit Years, dan salah satu kolom status.');
        $sheet->setCellValue('A7', '4. Format tanggal aman: dd/mm/yyyy, yyyy-mm-dd, atau nama bulan Indonesia.');
        $sheet->setCellValue('A8', '5. Status yang aman dipakai: Closed, On Progress, Domain Bank.');
        $sheet->setCellValue('D3', 'Tahun contoh');
        $sheet->setCellValue('D4', $year);

        $this->styleTitle($sheet, 'A1:F1');
        $this->styleSectionHeader($sheet, 'A3:B3');
        $sheet->getStyle('A4:A8')->getAlignment()->setWrapText(true);
        $this->setColumnWidths($sheet, [
            'A' => 78,
            'D' => 14,
        ]);
    }

    private function populateAuditTemplateSheet(Worksheet $sheet, int $year): void
    {
        $sheet->setCellValue('A1', 'Contoh Sheet Import Audit Monitoring');
        $sheet->mergeCells('A1:R1');

        $headers = [
            'A2' => 'Ref No Temuan',
            'B2' => 'Bank',
            'C2' => 'Audit Years',
            'D2' => 'Subject Audit',
            'E2' => 'Division',
            'F2' => 'Product',
            'G2' => 'Product Name',
            'H2' => 'Deskripsi',
            'I2' => 'PIC Penyelesaian',
            'J2' => 'Status Exit',
            'K2' => 'Status Detail',
            'L2' => 'Original Commitment Date',
            'M2' => 'Update Commitment Date',
            'N2' => 'Aging',
            'O2' => 'Maturity',
            'P2' => 'Maturity Days',
            'Q2' => 'Log CSS',
            'R2' => 'Nomor',
        ];

        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        $sheet->fromArray([
            'ATM-' . $year . '-001',
            'INTERNAL',
            $year,
            'Digital Banking',
            'Digital Banking',
            'Fraud Monitoring',
            'Fraud Monitoring Dashboard',
            'Update rule monitoring transaksi VA.',
            'Cyber Security',
            'On Progress',
            'On Progress 60%',
            '15/03/' . $year,
            '30/03/' . $year,
            '<=6 Bulan',
            '<=6 Bulan',
            45,
            'CSS-' . $year . '-001',
            1,
        ], null, 'A3');

        $sheet->fromArray([
            'ATM-' . $year . '-002',
            'INTERNAL',
            $year,
            'IT Operation',
            'IT Operation',
            'Patch Management',
            'Patch Compliance',
            'Finalisasi closing temuan patch server.',
            'Infrastructure',
            'Closed',
            'Closed 100%',
            '01/02/' . $year,
            '20/02/' . $year,
            '<=6 Bulan',
            '<=6 Bulan',
            20,
            'CSS-' . $year . '-002',
            2,
        ], null, 'A4');

        $this->styleTitle($sheet, 'A1:R1');
        $this->styleColumnHeader($sheet, 'A2:R2');
        $sheet->getStyle('A2:R4')->getAlignment()->setWrapText(true);
        $sheet->freezePane('A3');
        $sheet->setAutoFilter('A2:R2');
        $this->setColumnWidths($sheet, [
            'A' => 20,
            'B' => 14,
            'C' => 12,
            'D' => 20,
            'E' => 20,
            'F' => 18,
            'G' => 26,
            'H' => 34,
            'I' => 20,
            'J' => 16,
            'K' => 18,
            'L' => 18,
            'M' => 18,
            'N' => 14,
            'O' => 14,
            'P' => 14,
            'Q' => 16,
            'R' => 10,
        ]);
    }

    private function populateOjkReadmeSheet(Worksheet $sheet, int $year): void
    {
        $sheet->setCellValue('A1', 'Template Import Monitoring Mitigasi OJK');
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A3', 'Cara pakai');
        $sheet->setCellValue('A4', '1. Isi data pada sheet Sheet1.');
        $sheet->setCellValue('A5', '2. Header contoh ada di baris 2: NO, ACTION PLAN, PIC, SOLUSI, TARGET.');
        $sheet->setCellValue('A6', '3. TARGET bisa berupa tanggal atau status hasil seperti Done.');
        $sheet->setCellValue('A7', '4. Jika target belum selesai, sebaiknya isi tanggal target agar status tetap On Progress.');
        $sheet->setCellValue('D3', 'Tahun contoh');
        $sheet->setCellValue('D4', $year);

        $this->styleTitle($sheet, 'A1:F1');
        $this->styleSectionHeader($sheet, 'A3:B3');
        $sheet->getStyle('A4:A7')->getAlignment()->setWrapText(true);
        $this->setColumnWidths($sheet, [
            'A' => 78,
            'D' => 14,
        ]);
    }

    private function populateOjkTemplateSheet(Worksheet $sheet, int $year): void
    {
        $sheet->setCellValue('A1', 'Contoh Sheet Import Mitigasi OJK');
        $sheet->mergeCells('A1:E1');

        foreach ([
            'A2' => 'NO',
            'B2' => 'ACTION PLAN',
            'C2' => 'PIC',
            'D2' => 'SOLUSI',
            'E2' => 'TARGET',
        ] as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        $sheet->fromArray([
            1,
            'Aktivasi monitoring transaksi VA secara real-time.',
            'Cyber Security',
            'Integrasi rule ke FDS.',
            '10 Maret ' . $year,
        ], null, 'A3');

        $sheet->fromArray([
            2,
            'Penyesuaian validasi beneficiary.',
            'Digital Banking',
            'Release patch aplikasi.',
            'Done',
        ], null, 'A4');

        $this->styleTitle($sheet, 'A1:E1');
        $this->styleColumnHeader($sheet, 'A2:E2');
        $sheet->getStyle('A2:E4')->getAlignment()->setWrapText(true);
        $sheet->freezePane('A3');
        $sheet->setAutoFilter('A2:E2');
        $this->setColumnWidths($sheet, [
            'A' => 10,
            'B' => 40,
            'C' => 22,
            'D' => 32,
            'E' => 20,
        ]);
    }

    private function styleTitle(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1D4ED8');
        $sheet->getStyle($range)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function styleSectionHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
    }

    private function styleColumnHeader(Worksheet $sheet, string $range, string $fillColor = '1E40AF'): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF' . $fillColor);
        $sheet->getStyle($range)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    /**
     * @param  array<string, int|float>  $widths
     */
    private function setColumnWidths(Worksheet $sheet, array $widths): void
    {
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth((float) $width);
        }
    }

    /**
     * @return array<int, array{description: string, score: int}>
     */
    private function probabilityLookupRows(): array
    {
        return [
            ['description' => 'Sangat Tidak Mungkin (10 - 20%)', 'score' => 1],
            ['description' => 'Tidak Mungkin Terjadi (30 - 40%)', 'score' => 2],
            ['description' => 'Mungkin Terjadi (50 - 60%)', 'score' => 3],
            ['description' => 'Sangat Mungkin Terjadi (70 - 80%)', 'score' => 4],
            ['description' => 'Hampir Pasti (90 - 100%)', 'score' => 5],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function loadRiskCategories(): array
    {
        try {
            $items = DB::table('risk_categories')->orderBy('name')->pluck('name')->all();
            $items = $this->normalizeStringList($items);
            if (!empty($items)) {
                return $items;
            }
        } catch (\Throwable $e) {
            // ignore, fallback below
        }

        return ['Operasional', 'Kepatuhan', 'Teknis', 'Reputasi'];
    }

    /**
     * @return array<int, string>
     */
    private function loadRiskCauseCategories(): array
    {
        try {
            $items = DB::table('risk_cause_categories')->orderBy('name')->pluck('name')->all();
            $items = $this->normalizeStringList($items);
            if (!empty($items)) {
                return $items;
            }
        } catch (\Throwable $e) {
            // ignore, fallback below
        }

        return ['People', 'Process', 'Product', 'Nature'];
    }

    /**
     * @return array<int, array{code: string, description: string}>
     */
    private function loadRadarGroups(): array
    {
        try {
            $rows = DB::table('radar_groups')->orderBy('code')->get(['code', 'description']);
            $items = [];
            foreach ($rows as $row) {
                $code = trim((string) ($row->code ?? ''));
                $description = trim((string) ($row->description ?? ''));
                if ($code === '') {
                    continue;
                }
                $items[] = [
                    'code' => $code,
                    'description' => $description === '' ? $code : $description,
                ];
            }

            if (!empty($items)) {
                return $items;
            }
        } catch (\Throwable $e) {
            // ignore, fallback below
        }

        return [
            ['code' => 'O5', 'description' => 'O5 : Gagal dalam memberikan SLA'],
            ['code' => 'O4', 'description' => 'O4 : Gagal dalam memberikan solusi tepat waktu'],
            ['code' => 'N/A', 'description' => 'None'],
        ];
    }

    /**
     * @return array<int, array{description: string, score: int}>
     */
    private function loadImpactScales(): array
    {
        try {
            $rows = DB::table('impact_scales')
                ->orderBy('score')
                ->orderBy('description')
                ->get(['description', 'score']);

            $items = [];
            foreach ($rows as $row) {
                $description = trim((string) ($row->description ?? ''));
                $score = (int) ($row->score ?? 0);
                if ($description === '' || $score <= 0) {
                    continue;
                }
                $items[] = [
                    'description' => $description,
                    'score' => $score,
                ];
            }

            if (!empty($items)) {
                return $items;
            }
        } catch (\Throwable $e) {
            // ignore, fallback below
        }

        return [
            ['description' => 'Gangguan kecil', 'score' => 2],
            ['description' => 'Gangguan sedang', 'score' => 3],
            ['description' => 'Gangguan besar', 'score' => 4],
            ['description' => 'Operasional berhenti total', 'score' => 5],
        ];
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, string>
     */
    private function normalizeStringList(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $value = trim((string) $item);
            if ($value !== '') {
                $result[] = $value;
            }
        }

        return array_values(array_unique($result));
    }

    /**
     * @param  array<int, string>  $values
     * @param  array<int, string>  $preferred
     */
    private function pickPreferredValue(array $values, array $preferred): string
    {
        foreach ($preferred as $candidate) {
            foreach ($values as $value) {
                if (strcasecmp($value, $candidate) === 0) {
                    return $value;
                }
            }
        }

        return $values[0] ?? 'N/A';
    }

    /**
     * @param  array<int, array{description: string, score: int}>  $impactScales
     * @param  array<int, string>  $preferredDescriptions
     */
    private function pickPreferredImpact(array $impactScales, array $preferredDescriptions): string
    {
        foreach ($preferredDescriptions as $candidate) {
            foreach ($impactScales as $impactScale) {
                if (stripos($impactScale['description'], $candidate) !== false) {
                    return $impactScale['description'];
                }
            }
        }

        return $impactScales[0]['description'] ?? 'Gangguan sedang';
    }

    /**
     * @param  array<int, array{code: string, description: string}>  $radarGroups
     * @param  array<int, string>  $preferredCodes
     * @return array{code: string, description: string}
     */
    private function pickPreferredRadarGroup(array $radarGroups, array $preferredCodes): array
    {
        foreach ($preferredCodes as $candidate) {
            foreach ($radarGroups as $radarGroup) {
                if (strcasecmp($radarGroup['code'], $candidate) === 0) {
                    return $radarGroup;
                }
            }
        }

        return $radarGroups[0] ?? ['code' => 'N/A', 'description' => 'None'];
    }
}
