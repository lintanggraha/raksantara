<?php

namespace App\Http\Controllers;

use App\Services\ThreatIntelligenceService;
use Illuminate\Http\Request;

class ThreatIntelligenceController extends Controller
{
    private ThreatIntelligenceService $threatIntelligenceService;

    public function __construct(ThreatIntelligenceService $threatIntelligenceService)
    {
        $this->threatIntelligenceService = $threatIntelligenceService;
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'keyword' => 'nullable|string|max:100',
            'severity' => 'nullable|string|in:critical,high,medium,low,CRITICAL,HIGH,MEDIUM,LOW',
            'owasp' => 'nullable|string|in:A01,A02,A03,A04,A05,A06,A07,A08,A09,A10',
            'days' => 'nullable|integer|in:7,30,90',
            'limit' => 'nullable|integer|in:10,12,20,50',
        ]);

        $dashboard = $this->threatIntelligenceService->buildDashboard($filters);

        $breachesPath = public_path('data/breaches.json');
        $breaches = file_exists($breachesPath)
            ? (json_decode(file_get_contents($breachesPath), true) ?? [])
            : [];

        $pulses = [
            [
                'id' => 'PLS-01',
                'info' => 'Ivanti Connect Secure & Policy Secure Zero-Day Exploitation (CVE-2023-46805 & CVE-2024-21887)',
                'threat_level_id' => 1,
                'threat_label' => 'Critical',
                'author_name' => 'CISA Cybersecurity Advisory',
                'timestamp' => strtotime('-1 day'),
                'tags' => ['ivanti', 'vpn', 'rce', 'auth-bypass', 'cve-2024-21887'],
                'indicators_count' => 34,
                'link' => 'https://otx.alienvault.com/pulse/65a123b456',
            ],
            [
                'id' => 'PLS-02',
                'info' => 'Volt Typhoon Living-off-the-Land (LotL) Intrusion Infrastructure',
                'threat_level_id' => 1,
                'threat_label' => 'Critical',
                'author_name' => 'Microsoft Threat Intelligence',
                'timestamp' => strtotime('-2 days'),
                'tags' => ['apt', 'lotl', 'critical-infrastructure', 'stealth'],
                'indicators_count' => 18,
                'link' => 'https://otx.alienvault.com/pulse/65b234c567',
            ],
            [
                'id' => 'PLS-03',
                'info' => 'LockBit 3.0 Ransomware Campaign Targeting Indonesian Financial Sector',
                'threat_level_id' => 1,
                'threat_label' => 'Critical',
                'author_name' => 'AlienVault Community Feed',
                'timestamp' => strtotime('-3 days'),
                'tags' => ['ransomware', 'lockbit', 'banking', 'data-leak', 'double-extortion'],
                'indicators_count' => 62,
                'link' => 'https://otx.alienvault.com/pulse/65c345d678',
            ],
            [
                'id' => 'PLS-04',
                'info' => 'Palo Alto PAN-OS Command Injection Global Wave (CVE-2024-3400)',
                'threat_level_id' => 1,
                'threat_label' => 'Critical',
                'author_name' => 'Volexity Threat Research',
                'timestamp' => strtotime('-5 days'),
                'tags' => ['pan-os', 'firewall', 'zero-day', 'cve-2024-3400', 'backdoor'],
                'indicators_count' => 45,
                'link' => 'https://otx.alienvault.com/pulse/65d456e789',
            ],
            [
                'id' => 'PLS-05',
                'info' => 'Apache ActiveMQ OpenWire Remote Code Execution (CVE-2023-46604)',
                'threat_level_id' => 2,
                'threat_label' => 'High',
                'author_name' => 'Rapid7 Labs',
                'timestamp' => strtotime('-8 days'),
                'tags' => ['activemq', 'rce', 'kinsing', 'botnet'],
                'indicators_count' => 27,
                'link' => 'https://otx.alienvault.com/pulse/65e567f890',
            ],
            [
                'id' => 'PLS-06',
                'info' => 'Midnight Blizzard OAuth App Abuse & Token Theft Activity',
                'threat_level_id' => 2,
                'threat_label' => 'High',
                'author_name' => 'CrowdStrike Intelligence',
                'timestamp' => strtotime('-12 days'),
                'tags' => ['oauth', 'token-theft', 'cloud', 'identity', 'mfa-fatigue'],
                'indicators_count' => 15,
                'link' => 'https://otx.alienvault.com/pulse/65f678a901',
            ],
        ];

        $iocs = [
            [
                'id' => 'IOC-01',
                'type' => 'IPv4',
                'value' => '194.26.29.112',
                'sightings' => 142,
                'event_title' => 'Ivanti Connect Secure Zero-Day Wave',
                'tags' => ['c2-server', 'scanner', 'vpn-exploit'],
                'contributor' => 'CISA',
                'timestamp' => strtotime('-1 day'),
            ],
            [
                'id' => 'IOC-02',
                'type' => 'SHA256',
                'value' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
                'sightings' => 89,
                'event_title' => 'LockBit 3.0 Dropper Payload',
                'tags' => ['ransomware', 'malware', 'payload'],
                'contributor' => 'AlienVault',
                'timestamp' => strtotime('-2 days'),
            ],
            [
                'id' => 'IOC-03',
                'type' => 'Domain',
                'value' => 'update-telemetry-service.com',
                'sightings' => 64,
                'event_title' => 'Volt Typhoon LotL Staging Domain',
                'tags' => ['apt', 'c2-domain', 'staging'],
                'contributor' => 'Microsoft TI',
                'timestamp' => strtotime('-3 days'),
            ],
            [
                'id' => 'IOC-04',
                'type' => 'IPv4',
                'value' => '45.142.214.21',
                'sightings' => 218,
                'event_title' => 'Apache ActiveMQ Kinsing Botnet Node',
                'tags' => ['miner', 'botnet', 'brute-force'],
                'contributor' => 'Rapid7',
                'timestamp' => strtotime('-4 days'),
            ],
            [
                'id' => 'IOC-05',
                'type' => 'SHA256',
                'value' => '7f83b1657ff1fc53b92dc18148a1d65dfc2d4b1fa3d677284addd200126d9069',
                'sightings' => 47,
                'event_title' => 'Palo Alto PAN-OS Webshell Artifact',
                'tags' => ['webshell', 'persistence', 'backdoor'],
                'contributor' => 'Volexity',
                'timestamp' => strtotime('-5 days'),
            ],
            [
                'id' => 'IOC-06',
                'type' => 'URL',
                'value' => 'http://login.service-auth-verify.net/session',
                'sightings' => 35,
                'event_title' => 'Credential Harvesting targeting Indonesian Banking Staff',
                'tags' => ['phishing', 'credential-harvesting'],
                'contributor' => 'CrowdStrike',
                'timestamp' => strtotime('-6 days'),
            ],
            [
                'id' => 'IOC-07',
                'type' => 'IPv4',
                'value' => '185.220.101.5',
                'sightings' => 310,
                'event_title' => 'Tor Exit Node Active Scanning Financial APIs',
                'tags' => ['tor-exit', 'reconnaissance', 'api-abuse'],
                'contributor' => 'AlienVault',
                'timestamp' => strtotime('-7 days'),
            ],
        ];

        return view('threat-intelligence.index', compact('dashboard', 'breaches', 'pulses', 'iocs'));
    }

    public function searchBreaches(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $breachesPath = public_path('data/breaches.json');
        $breaches = file_exists($breachesPath)
            ? (json_decode(file_get_contents($breachesPath), true) ?? [])
            : [];

        if ($query === '') {
            return response()->json(['results' => $breaches]);
        }

        $lowerQuery = strtolower($query);
        $results = array_values(array_filter($breaches, function ($item) use ($lowerQuery) {
            $name = strtolower($item['Name'] ?? '');
            $domain = strtolower($item['Domain'] ?? '');
            $desc = strtolower($item['Description'] ?? '');
            $country = strtolower($item['Country'] ?? '');
            $classes = strtolower(implode(' ', (array) ($item['DataClasses'] ?? [])));

            return str_contains($name, $lowerQuery)
                || str_contains($domain, $lowerQuery)
                || str_contains($desc, $lowerQuery)
                || str_contains($country, $lowerQuery)
                || str_contains($classes, $lowerQuery);
        }));

        return response()->json(['results' => $results]);
    }

    public function cveAi(Request $request)
    {
        $validated = $request->validate([
            'cve' => 'required|array',
            'cve.id' => 'required|string',
            'message' => 'required|string|max:1500',
        ]);

        $cve = $validated['cve'];
        $message = trim($validated['message']);

        $response = $this->generateDefensiveAiResponse($cve, $message);

        return response()->json([
            'success' => true,
            'response' => $response,
        ]);
    }

    private function generateDefensiveAiResponse(array $cve, string $message): string
    {
        $id = $cve['id'] ?? 'CVE';
        $title = $cve['title'] ?? 'Kerentanan Sistem';
        $vendor = $cve['vendor'] ?? 'Vendor';
        $product = $cve['product'] ?? 'Produk';
        $reqAction = $cve['requiredAction'] ?? $cve['required_action'] ?? 'Terapkan update keamanan resmi vendor.';
        $isRansom = !empty($cve['isRansomware']) || !empty($cve['known_ransomware_campaign_use']);
        $lowerMsg = strtolower($message);

        $out = "";

        if (str_contains($lowerMsg, 'ringkasan') || str_contains($lowerMsg, 'eksekutif')) {
            $out = "📌 **Executive Briefing untuk {$id} ({$vendor} {$product})**\n\n";
            $out .= "• **Sifat Ancaman**: Tergolong kerentanan aktif tereksploitasi (CISA KEV Catalog). " . ($isRansom ? "⚠️ Terindikasi digunakan dalam kampanye Ransomware aktif!" : "Belum tercatat kampanye ransomware massal, namun eksploitasi aktif terdeteksi di perimeter publik.") . "\n";
            $out .= "• **Deskripsi Masalah**: {$title}\n";
            $out .= "• **Tindakan Mandatori**: {$reqAction}\n";
            $out .= "• **Korelasi Regulasi**: Menuntut penanganan tanggap insiden maksimal 14 hari kalender berdasarkan panduan resiliensi siber OJK dan POJK 11/2022 Pasal 28.";
            return $out;
        }

        if (str_contains($lowerMsg, 'dampak') || str_contains($lowerMsg, 'indonesia') || str_contains($lowerMsg, 'bank')) {
            $out = "🔍 **Analisis Dampak untuk Organisasi & Sektor Finansial di Indonesia ({$id})**\n\n";
            $out .= "1. **Risiko Operasional & Layanan Perbankan**:\n";
            $out .= "   Jika instansi Anda menjalankan {$product} dari {$vendor} pada perimeter jaringan eksternal/DMZ, eksploitasi kerentanan ini dapat menyebabkan terganggunya ketersediaan layanan transaksi finansial dan potensi pengambilalihan hak akses sistem host.\n\n";
            $out .= "2. **Risiko Kerahasiaan Data (UU PDP No. 27/2022)**:\n";
            $out .= "   Bila penyerang berhasil menembus perimeter melalui kerentanan ini dan menjangkau basis data pelanggan, insiden ini wajib dilaporkan dalam waktu **maksimal 3 x 24 jam** ke Lembaga Perlindungan Data Pribadi dan Subjek Data (Pasal 46 UU PDP).\n\n";
            $out .= "3. **Implikasi Audit OJK (POJK 11/2022 & PADK 1/2026)**:\n";
            $out .= "   Pengabaian patch terhadap kerentanan CISA KEV berpotensi menjadi temuan mayor dalam audit kepatuhan ketahanan siber tahunan OJK.";
            return $out;
        }

        if (str_contains($lowerMsg, 'mitigasi') || str_contains($lowerMsg, 'hardening') || str_contains($lowerMsg, 'cara')) {
            $out = "🛡️ **Rekomendasi Langkah Mitigasi & Defensive Hardening ({$id})**\n\n";
            $out .= "1. **Immediate Patch Deployment (Prioritas 1)**:\n";
            $out .= "   - {$reqAction}\n";
            $out .= "   - Verifikasi checksum binary sebelum deploy ke lingkungan staging dan production.\n\n";
            $out .= "2. **Perimeter Isolation & Compensating Controls**:\n";
            $out .= "   - Bila patch belum dapat diterapkan seketika karena *change freeze*, batasi eksposur layanan ke internet publik hanya melalui IP Allowlist atau VPN dengan MFA.\n";
            $out .= "   - Aktifkan rule deteksi virtual patching pada Web Application Firewall (WAF) / Next-Gen Firewall (NGFW).\n\n";
            $out .= "3. **Monitoring & Threat Hunting**:\n";
            $out .= "   - Periksa log akses HTTP, event log autentikasi, dan anomali koneksi egress dari server {$product}.\n";
            $out .= "   - Terapkan pemantauan integritas file (FIM) untuk mendeteksi kemungkinan penempatan webshell.";
            return $out;
        }

        if (str_contains($lowerMsg, 'ransomware') || str_contains($lowerMsg, 'exploit')) {
            $out = "⚠️ **Analisis Kampanye Ransomware & Ketersediaan Exploit ({$id})**\n\n";
            if ($isRansom) {
                $out .= "• **Status KEV**: **DIKONFIRMASI TERLIBAT DALAM KAMPANYE RANSOMWARE**.\n";
                $out .= "• Kerentanan ini telah diadopsi oleh sindikat ransomware (seperti LockBit, BlackCat/ALPHV, atau Phobos) sebagai initial access broker (IAB).\n";
                $out .= "• **Rekomendasi Kritis**: Segera lakukan isolasi perangkat jika terdeteksi aktivitas proses yang tidak lazim dan pastikan backup *immutable / offline* (3-2-1 backup rule) telah tervalidasi.";
            } else {
                $out .= "• **Status KEV**: Kerentanan ini telah terbukti dieksploitasi di alam liar (In-The-Wild), namun saat ini CISA belum mengkategorikannya sebagai bagian dari kampanye ransomware massal tertentu.\n";
                $out .= "• Meskipun demikian, ketersediaan Proof-of-Concept (PoC) publik biasanya memicu adopsi cepat oleh aktor ransomware dalam 7-14 hari.";
            }
            return $out;
        }

        if (str_contains($lowerMsg, 'regulasi') || str_contains($lowerMsg, 'pasal') || str_contains($lowerMsg, 'standar')) {
            $out = "📋 **Pemetaan Standar Regulasi & Kepatuhan Terkait ({$id})**\n\n";
            $out .= "• **POJK 11/2022**: Pasal 28 (Kewajiban Pengujian Kerentanan & Penilaian Keamanan Sistem Berkala).\n";
            $out .= "• **PADK 1/2026**: Aspek Ketahanan Siber & Siklus Manajemen Patching Aset Kritis Perbankan.\n";
            $out .= "• **ISO/IEC 27001:2022**: Klausul Kontrol A.8.8 (*Management of Technical Vulnerabilities*) & A.8.19 (*Installation of software on operational systems*).\n";
            $out .= "• **NIST CSF 2.0**: Kategori PR.PS (Platform Security) & DE.CM (Continuous Monitoring).\n";
            $out .= "• **UU PDP No. 27/2022**: Pasal 35 (Kewajiban Pelindungan Data dari Akses Tanpa Hak).";
            return $out;
        }

        // Generic intelligent defensive fallback
        $out = "🤖 **Analisis Defensif Raksantara AI untuk {$id}**\n\n";
        $out .= "Kerentanan pada **{$vendor} {$product}** ini terdaftar dalam katalog kerentanan berisiko tinggi. Pertanyaan Anda: *\"{$message}\"*\n\n";
        $out .= "• **Prioritas Aksi**: Lakukan inventarisasi aset untuk memastikan ada/tidaknya instans {$product} yang berjalan di infrastruktur lokal maupun cloud Anda.\n";
        $out .= "• **Langkah Cepat**: {$reqAction}\n";
        $out .= "• **Kepatuhan Audit**: Simpan catatan *change request* dan bukti deployment patch sebagai artefak audit pemenuhan POJK 11/2022 dan ISO 27001 A.8.8.";

        return $out;
    }
}

