<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalysisController extends Controller
{
    public function __construct()
    {
        $this->middleware('superadmin.or.compliance');
    }

    /**
     * Display Cross-Mapping between cybersecurity frameworks and regulations.
     */
    public function crossMapping()
    {
        $mappingData = Cache::remember('cross_mapping_data_v2', 3600, function () {
            $dataPath = public_path('data/cross_mapping_data.json');
            return file_exists($dataPath)
                ? (json_decode(file_get_contents($dataPath), true) ?? [])
                : [];
        });

        return view('analysis.cross-mapping', compact('mappingData'));
    }

    /**
     * Display side-by-side comparison and gap analysis between frameworks.
     */
    public function comparison(Request $request)
    {
        $comparisonData = Cache::remember('comparison_data_v2', 3600, function () {
            $compPath = public_path('data/comparison_data.json');
            return file_exists($compPath)
                ? (json_decode(file_get_contents($compPath), true) ?? [])
                : [];
        });

        $gapData = Cache::remember('gap_data_v2', 3600, function () {
            $gapPath = public_path('data/gap_data.json');
            return file_exists($gapPath)
                ? (json_decode(file_get_contents($gapPath), true) ?? [])
                : [];
        });

        $defaultFrameworkA = $request->query('fwA', 'iso27001');
        $defaultFrameworkB = $request->query('fwB', 'nist');

        return view('analysis.comparison', compact('comparisonData', 'gapData', 'defaultFrameworkA', 'defaultFrameworkB'));
    }

    /**
     * Display self-assessment checklist tool with Excel export/import capabilities.
     */
    public function checklist()
    {
        $availableFrameworks = [
            [
                'id' => 'iso27001',
                'name' => 'ISO 27001:2022',
                'desc' => 'Standar Internasional Sistem Manajemen Keamanan Informasi (ISMS)',
                'icon' => 'fa-shield-alt',
                'file' => 'iso27001.json',
                'color' => '#0d6efd'
            ],
            [
                'id' => 'iso37001',
                'name' => 'ISO 37001:2016',
                'desc' => 'Sistem Manajemen Anti-Penyuapan (ABMS)',
                'icon' => 'fa-handshake',
                'file' => 'iso37001.json',
                'color' => '#10b981'
            ],
            [
                'id' => 'nist',
                'name' => 'NIST CSF 2.0',
                'desc' => 'National Institute of Standards and Technology Cybersecurity Framework',
                'icon' => 'fa-compass',
                'file' => 'nist_csf.json',
                'color' => '#198754'
            ],
            [
                'id' => 'cobit',
                'name' => 'COBIT 2019',
                'desc' => 'Framework Tata Kelola & Manajemen TI Perusahaan (ISACA)',
                'icon' => 'fa-project-diagram',
                'file' => 'cobit_2019.json',
                'color' => '#fd7e14'
            ],
            [
                'id' => 'seojk',
                'name' => 'SEOJK 29/03/2022',
                'desc' => 'Ketahanan Siber dan Manajemen Risiko TI Perbankan OJK',
                'icon' => 'fa-landmark',
                'file' => 'seojk_requirements.json',
                'color' => '#dc3545'
            ],
            [
                'id' => 'pojk11',
                'name' => 'POJK 11/2022',
                'desc' => 'Penyelenggaraan Teknologi Informasi oleh Bank Umum',
                'icon' => 'fa-microchip',
                'file' => 'pojk_11_2022_requirements.json',
                'color' => '#6610f2'
            ],
            [
                'id' => 'padk',
                'name' => 'PADK 1 Tahun 2026',
                'desc' => 'Ketentuan Pelaksana Penyelenggaraan TI Bank Umum OJK 2026',
                'icon' => 'fa-server',
                'file' => 'padk_1_2026_requirements.json',
                'color' => '#0f766e'
            ],
            [
                'id' => 'pbi',
                'name' => 'PBI 02/2024',
                'desc' => 'Keamanan dan Ketahanan Siber (KKS) Sistem Pembayaran Bank Indonesia',
                'icon' => 'fa-university',
                'file' => 'pbi_022024_requirements.json',
                'color' => '#0dcaf0'
            ],
            [
                'id' => 'padg',
                'name' => 'PADG 32/2025',
                'desc' => 'Pengaturan Industri Penyelenggaraan Sistem Pembayaran BI',
                'icon' => 'fa-credit-card',
                'file' => 'padg_requirements.json',
                'color' => '#d97706'
            ],
            [
                'id' => 'padg24',
                'name' => 'PADG 24/2024',
                'desc' => 'Keamanan Sistem Informasi dan Ketahanan Siber BI',
                'icon' => 'fa-shield-halved',
                'file' => 'padg24_2024_requirements.json',
                'color' => '#2563eb'
            ],
            [
                'id' => 'owasp_top10',
                'name' => 'OWASP Top 10 2025',
                'desc' => '10 Risiko Keamanan Aplikasi Web Paling Kritis',
                'icon' => 'fa-bug',
                'file' => 'owasp_top10_reqs.json',
                'color' => '#ef4444'
            ],
            [
                'id' => 'owasp_asvs',
                'name' => 'OWASP ASVS 5.0.0',
                'desc' => 'Application Security Verification Standard untuk Audit Aplikasi',
                'icon' => 'fa-check-double',
                'file' => 'owasp_asvs_reqs.json',
                'color' => '#059669'
            ],
            [
                'id' => 'pdp',
                'name' => 'UU PDP No. 27/2022',
                'desc' => 'Undang-Undang Pelindungan Data Pribadi Republik Indonesia',
                'icon' => 'fa-user-shield',
                'file' => 'uu_pdp_requirements.json',
                'color' => '#4f46e5'
            ],
        ];

        return view('analysis.checklist', compact('availableFrameworks'));
    }

    /**
     * Display "What-If" compliance simulator and scenario engine.
     */
    public function simulator()
    {
        return view('analysis.simulator');
    }
}
