<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class FrameworkController extends Controller
{
    public function iso27001()
    {
        $domainStats = [
            ['id' => 'ORG', 'name' => 'Organisasional', 'count' => 37, 'color' => '#10b981'],
            ['id' => 'PPL', 'name' => 'Orang', 'count' => 8, 'color' => '#3b82f6'],
            ['id' => 'PHY', 'name' => 'Fisik', 'count' => 14, 'color' => '#a855f7'],
            ['id' => 'TEC', 'name' => 'Teknologi', 'count' => 34, 'color' => '#f59e0b'],
        ];

        $jsonPath = storage_path('app/iso27001.json');
        if (file_exists($jsonPath)) {
            $controls = json_decode(file_get_contents($jsonPath), true) ?? [];
        } else {
            $controls = [];
        }

        $controls = array_map(fn ($control) => $this->normalizeIsoControl($control), $controls);

        return view('frameworks.iso27001', compact('domainStats', 'controls'));
    }

    public function nist()
    {
        $jsonPath = public_path('data/nist_csf.json');
        $controls = file_exists($jsonPath)
            ? (json_decode(file_get_contents($jsonPath), true) ?? [])
            : [];

        $controls = array_map(fn ($control) => $this->normalizeNistControl($control), $controls);

        $sp800Path = public_path('data/sp800_53.json');
        $sp800Controls = file_exists($sp800Path)
            ? (json_decode(file_get_contents($sp800Path), true) ?? [])
            : [];

        return view('frameworks.nist', compact('controls', 'sp800Controls'));
    }

    public function seojk()
    {
        $requirementsPath = public_path('data/seojk_requirements.json');
        $requirements = file_exists($requirementsPath)
            ? (json_decode(file_get_contents($requirementsPath), true) ?? [])
            : [];

        $requirements = array_map(fn ($requirement) => $this->normalizeSeojkRequirement($requirement), $requirements);

        $appendicesPath = public_path('data/seojk_appendices.json');
        $appendices = file_exists($appendicesPath)
            ? (json_decode(file_get_contents($appendicesPath), true) ?? [])
            : [];

        return view('frameworks.seojk', compact('requirements', 'appendices'));
    }

    public function cis()
    {
        $guidancePath = public_path('data/seojk_resilience_guidance.json');
        $guidanceThemes = file_exists($guidancePath)
            ? (json_decode(file_get_contents($guidancePath), true) ?? [])
            : [];

        $guidanceThemes = array_map(fn ($guidance) => $this->normalizeResilienceGuidance($guidance), $guidanceThemes);

        return view('frameworks.ojk-resilience', compact('guidanceThemes'));
    }

    public function pbi()
    {
        $requirementsPath = public_path('data/pbi_022024_requirements.json');
        $requirements = file_exists($requirementsPath)
            ? (json_decode(file_get_contents($requirementsPath), true) ?? [])
            : [];

        $requirements = array_map(fn ($requirement) => $this->normalizePbiRequirement($requirement), $requirements);

        $referencesPath = public_path('data/pbi_022024_references.json');
        $references = file_exists($referencesPath)
            ? (json_decode(file_get_contents($referencesPath), true) ?? [])
            : [];

        $references = array_map(function ($reference) {
            $reference['deliverables'] = array_values($reference['deliverables'] ?? []);
            $reference['linked_requirements'] = array_values($reference['linked_requirements'] ?? []);

            return $reference;
        }, $references);

        return view('frameworks.pbi', compact('requirements', 'references'));
    }

    public function placeholder()
    {
        return abort(404, 'Framework framework belum tersedia di versi ini.');
    }

    protected function normalizeIsoControl(array $control): array
    {
        $control['interpretation'] = $this->buildIsoInterpretation($control);
        $control['pandaExplanation'] = $control['interpretation'];

        return $control;
    }

    protected function normalizeNistControl(array $control): array
    {
        $control['interpretation'] = $this->buildNistInterpretation($control);
        $control['panda_explanation'] = $control['interpretation'];

        return $control;
    }

    protected function normalizeSeojkRequirement(array $requirement): array
    {
        $requirement['interpretation'] = $this->buildSeojkInterpretation($requirement);
        $requirement['panda_explanation'] = $requirement['interpretation'];

        return $requirement;
    }

    protected function normalizeResilienceGuidance(array $guidance): array
    {
        $guidance['focus'] = array_values($guidance['focus'] ?? []);
        $guidance['interpretation'] = $this->buildResilienceGuidanceInterpretation($guidance);
        $guidance['focus_count'] = count($guidance['focus']);

        return $guidance;
    }

    protected function normalizePbiRequirement(array $requirement): array
    {
        $requirement['focus'] = array_values($requirement['focus'] ?? []);
        $requirement['evidence'] = array_values($requirement['evidence'] ?? []);
        $requirement['interpretation'] = $this->buildPbiInterpretation($requirement);
        $requirement['panda_explanation'] = $requirement['interpretation'];
        $requirement['focus_count'] = count($requirement['focus']);
        $requirement['evidence_count'] = count($requirement['evidence']);

        return $requirement;
    }

    protected function buildIsoInterpretation(array $control): string
    {
        $name = Str::lower($control['name'] ?? 'kontrol ini');
        $domainFocus = match ($control['domain'] ?? null) {
            'Organisasional' => 'Perhatian utamanya berada pada kebijakan, tata kelola, penetapan tanggung jawab, dan mekanisme pengawasan yang terdokumentasi.',
            'Orang' => 'Penerapannya perlu terlihat pada perilaku personel, kejelasan peran, dan konsistensi kepatuhan dalam aktivitas sehari-hari.',
            'Fisik' => 'Implementasinya perlu tercermin pada perlindungan lokasi, perangkat, dan lingkungan operasional yang relevan.',
            'Teknologi' => 'Implementasinya perlu tercermin pada konfigurasi sistem, kontrol akses, monitoring, dan pengelolaan platform teknis secara konsisten.',
            default => 'Implementasinya perlu didukung bukti yang jelas dan peninjauan berkala oleh pihak yang bertanggung jawab.',
        };

        $controlPurpose = match ($control['type'] ?? null) {
            'Pencegahan' => 'Kontrol ini terutama diarahkan untuk mengurangi kemungkinan terjadinya insiden sebelum berdampak pada operasi.',
            'Deteksi' => 'Kontrol ini membantu organisasi mengenali penyimpangan atau kejadian secara lebih dini.',
            'Koreksi' => 'Kontrol ini mendukung penanganan dan pemulihan setelah kejadian terjadi.',
            default => 'Kontrol ini perlu dijalankan secara konsisten dan dapat diverifikasi.',
        };

        return 'Kontrol ini menegaskan bahwa ' . $name . ' perlu diterapkan secara konsisten, didukung bukti yang dapat diverifikasi, dan ditinjau berkala oleh pihak yang bertanggung jawab. '
            . $domainFocus . ' ' . $controlPurpose;
    }

    protected function buildNistInterpretation(array $control): string
    {
        $title = Str::lower($control['title'] ?? 'subkategori ini');
        $functionFocus = match ($control['function'] ?? null) {
            'GOVERN' => 'Fokus utamanya adalah arah kebijakan, akuntabilitas, dan keputusan manajemen yang dapat ditelusuri.',
            'IDENTIFY' => 'Fokus utamanya adalah pemahaman aset, konteks bisnis, dan paparan risiko yang relevan.',
            'PROTECT' => 'Fokus utamanya adalah penerapan pengamanan operasional dan teknis yang berjalan konsisten.',
            'DETECT' => 'Fokus utamanya adalah kemampuan memantau, mengenali anomali, dan mengeskalasi indikasi kejadian.',
            'RESPOND' => 'Fokus utamanya adalah koordinasi tindakan, komunikasi, dan pengendalian dampak saat insiden terjadi.',
            'RECOVER' => 'Fokus utamanya adalah pemulihan layanan, kesinambungan operasi, dan pembelajaran pasca insiden.',
            default => 'Fokus utamanya adalah penerapan kontrol yang terukur dan dapat dibuktikan.',
        };

        return 'Subkategori ini menekankan bahwa ' . $title . ' perlu diterapkan sebagai bagian dari proses operasional yang terukur. '
            . $functionFocus . ' Bukti implementasi sebaiknya menunjukkan owner yang jelas, prosedur yang berjalan, dan hasil pengendalian yang dapat diverifikasi.';
    }

    protected function buildSeojkInterpretation(array $requirement): string
    {
        $title = Str::lower($requirement['title'] ?? 'kewajiban ini');
        $pillarFocus = match ($requirement['pillar'] ?? null) {
            'Assessment' => 'Penekanannya ada pada disiplin penilaian, konsistensi metodologi, dan justifikasi skor yang dapat ditelusuri.',
            'Governance' => 'Penekanannya ada pada arah kebijakan, akuntabilitas pengurus, dan tata kelola yang dapat diaudit.',
            'Resilience' => 'Penekanannya ada pada kontrol operasional yang menjaga identifikasi, pelindungan, deteksi, dan pemulihan berjalan nyata.',
            'Testing' => 'Penekanannya ada pada pengujian berkala yang membuktikan kesiapan teknis maupun kesiapan respons organisasi.',
            'Organization' => 'Penekanannya ada pada mandat fungsi, independensi, dan koordinasi lintas unit saat menghadapi insiden siber.',
            'Reporting' => 'Penekanannya ada pada konsolidasi hasil penilaian menjadi tingkat risiko akhir dan pelaporan resmi ke regulator.',
            default => 'Penekanannya ada pada penerapan yang terukur dan dapat diverifikasi.',
        };

        $reportingFocus = filled($requirement['reporting'] ?? null)
            ? ' ' . $requirement['reporting']
            : '';

        return 'Kewajiban ini menegaskan bahwa ' . $title . ' perlu dijalankan dengan owner yang jelas, artefak yang terdokumentasi, dan pengawasan yang konsisten. '
            . $pillarFocus . ' Bukti implementasi sebaiknya menunjukkan jadwal pelaksanaan, hasil evaluasi, dan tindak lanjut yang dapat diverifikasi.'
            . $reportingFocus;
    }

    protected function buildResilienceGuidanceInterpretation(array $guidance): string
    {
        $title = Str::lower($guidance['title'] ?? 'tema panduan ini');
        $sectionFocus = match ($guidance['section'] ?? null) {
            'A. Latar Belakang' => 'Bagian ini menekankan konteks strategis, eksposur gangguan, dan pentingnya kesinambungan layanan digital.',
            'B. Ketahanan Digital' => 'Bagian ini menekankan kemampuan mencegah, mendeteksi, merespons, dan memulihkan gangguan pada layanan digital.',
            'C. Kerangka Ketahanan Digital' => 'Bagian ini menekankan tata kelola, struktur pengelolaan, pengujian, dan peningkatan berkelanjutan.',
            default => 'Bagian ini menekankan penguatan kapasitas ketahanan digital secara terstruktur.',
        };

        return 'Tema ini menegaskan bahwa ' . $title . ' perlu diterjemahkan menjadi pengaturan operasional yang jelas, prioritas implementasi yang terukur, dan tindak lanjut yang terdokumentasi. '
            . $sectionFocus . ' Bukti implementasi sebaiknya menunjukkan kebijakan yang relevan, pengaturan proses yang berjalan, dan evaluasi berkala atas efektivitasnya.';
    }

    protected function buildPbiInterpretation(array $requirement): string
    {
        $title = Str::lower($requirement['title'] ?? 'kewajiban ini');
        $domainFocus = match ($requirement['domain'] ?? null) {
            'Kerangka' => 'Penekanannya ada pada sasaran pengaturan, prinsip dasar, objek pengaturan, dan ruang lingkup KKS yang menjadi fondasi penerapan.',
            'Tata Kelola' => 'Penekanannya ada pada strategi, kebijakan, fungsi organisasi, manajemen risiko siber, audit, dan budaya KKS.',
            'Pencegahan' => 'Penekanannya ada pada profil risiko siber, pengamanan berlapis, pelindungan data, deteksi, dan pengujian berkala.',
            'Penanganan' => 'Penekanannya ada pada kesiapan respons, komunikasi, pemulihan layanan, dan perbaikan berkelanjutan saat insiden terjadi.',
            'Pengawasan' => 'Penekanannya ada pada keterlacakan data, mekanisme pengawasan langsung dan tidak langsung, serta tindak lanjut pengawasan Bank Indonesia.',
            'Kolaborasi' => 'Penekanannya ada pada pertukaran informasi, koordinasi lintas otoritas, dan kerja sama operasional melalui SRO.',
            'Penerapan' => 'Penekanannya ada pada penerapan KKS sesuai klasifikasi Penyelenggara dan profil risiko yang relevan.',
            default => 'Penekanannya ada pada penerapan yang terukur dan dapat diverifikasi.',
        };

        $reportingFocus = filled($requirement['reporting'] ?? null)
            ? ' ' . $requirement['reporting']
            : '';

        return 'Kewajiban ini menegaskan bahwa ' . $title . ' perlu dijalankan melalui pengaturan operasional yang terdokumentasi, owner yang jelas, dan evaluasi berkala. '
            . $domainFocus . ' Bukti implementasi sebaiknya menunjukkan kebijakan yang berlaku, artefak pelaksanaan, dan tindak lanjut atas hasil evaluasi yang dapat diverifikasi.'
            . $reportingFocus;
    }
}
