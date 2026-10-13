<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SumutChecklistItem extends Model
{
    protected $table = 'sumut_checklist_items';

    protected $fillable = [
        'point_number',
        'category',
        'title',
        'clean_title',
        'progress',
        'progress_percent',
        'progress_2509',
        'progress_2609',
        'keterangan',
        'target_date',
        'pic',
        'status_bs',
        'progress_bs',
        'follow_up',
        'status',
        'notes',
        'recommendation',
        'sort_order',
    ];

    /**
     * Evidences attached to this checklist point
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(SumutChecklistEvidence::class, 'checklist_item_id')->orderBy('id', 'asc');
    }

    /**
     * Get clean title attribute
     */
    public function getCleanTitleAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        return static::sanitizeTitle($this->title);
    }

    /**
     * Sanitize title for display
     */
    public static function sanitizeTitle(string $title): string
    {
        $lines = preg_split('/[\r\n]+/', trim($title));
        $firstLine = trim($lines[0] ?? '');
        $firstLine = rtrim($firstLine, ':');
        $sanitized = preg_replace('/\s+/', ' ', $firstLine);
        $clean = trim($sanitized, " \"'\t\n\r\0\x0B");

        if (mb_strlen($clean) > 200) {
            $clean = mb_substr($clean, 0, 197) . '...';
        }

        return $clean ?: 'Point Checklist';
    }

    /**
     * Status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Ada' => 'bg-green-lt text-green border-green',
            default => 'bg-red-lt text-red border-red',
        };
    }

    /**
     * Status icon
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'Ada' => 'fas fa-check-circle',
            default => 'fas fa-times-circle',
        };
    }

    /**
     * Category badge color
     */
    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'CBS & Integritas Transaksi' => 'blue',
            'Sistem Operasi & Database Hardening' => 'indigo',
            'Containment & Endpoint Security (EDR)' => 'purple',
            'Fraud Detection & Early Warning System' => 'orange',
            'Akses Kontrol, PAM & Integrasi API' => 'teal',
            'Keamanan Perimeter, Firewall & WAF' => 'azure',
            'Rekonstruksi Sistem, HSM & Solusi Lanjutan' => 'pink',
            default => 'secondary',
        };
    }

    /**
     * Format folder number (e.g. 01, 02)
     */
    public function getFolderNumberAttribute(): string
    {
        return sprintf("%02d", $this->point_number);
    }

    /**
     * Get specific missing evidence items to reach 100%
     */
    public function getMissingEvidenceListAttribute(): array
    {
        return match ($this->point_number) {
            8 => [
                'Timeline & Roadmap Detail Setiap Fase Micro-segmentasi (fase pengadaan perangkat, uji coba internal, hingga go-live migrasi segmen jaringan).',
                'Rule dump / backup iptables-save per VM untuk membuktikan aturan isolasi port database aktif di seluruh VM target.',
                'Diagram Topologi Jaringan Micro-segmentasi Final yang memperlihatkan pemisahan vLAN/segmen IP baru setelah pengadaan fabric micro-segmentasi selesai.',
            ],
            11 => [
                'Berita Acara (BA) Instalasi EDR Terbaru khusus server Rebuild ManageEngine (dengan tanggal instalasi pasca-21 Agustus 2026).',
                'Bukti/BA Instalasi EDR pada 19 Server Tersisa (penyelesaian status "EDR Not Yet" menjadi 100% Installed).',
                'Dokumen Resmi Total Inventaris Server Bank Sumut (Hardware & VM inventory resmi yang ditandatangani).',
                'Export Laporan Status Agen (Agent Health & Policy Status) dari Konsol Pusat Sangfor EPP / Wazuh berstatus Online & Up-to-Date 100%.',
            ],
            17 => [
                'Screenshot & Log Fitur Anti-Tampering Protection EDR (bukti fitur proteksi uninstall password & proteksi service EDR agar tidak bisa dimatikan/di-disable oleh local admin).',
                'Bukti FIM (File Integrity Monitoring) pada file /etc/ssh/sshd_config (rule Wazuh / Sangfor yang memicu alert jika file konfigurasi SSH diubah).',
                'Laporan Audit Vulnerability Scanning / OpenSCAP Compliance terhadap SSH Baseline di seluruh kluster core & payment.',
            ],
            19 => [
                'Daftar Pemetaan IP Jaringan Cabang Resmi (Branch IP Whitelist Matrix) yang telah disetujui.',
                'Config Dump / Screenshot Firewall CBS Terbaru yang membuktikan source address telah dibatasi secara spesifik hanya untuk IP cabang terdaftar (bukan subnet global/any).',
            ],
            24 => [
                'Dokumen Hasil Uji Coba Teknis / Proof of Concept (PoC) Message Signing antar-modul aplikasi OLIBS dengan simulasi signing/verifying payload transaksi.',
                'Berita Acara Pengadaan & Delivery Perangkat Fisik HSM (Hardware Security Module).',
                'Laporan Progres Implementasi / BAST Uji Integrasi HSM ke OLIBS Gateway menjelang target W4 Oktober 2026.',
            ],
            27 => [
                'Laporan Assessment Awal Keamanan Siber (Initial Cybersecurity Health Check / Gap Analysis Report) yang disusun oleh konsultan eksternal (CIP-UBL).',
                'Laporan Berkala Capaian Deliverables (Progress Report Tahap 2) sesuai milestone pada Pasal 6 PKS.',
                'Notulen Rapat Koordinasi Mingguan / Weekly Steering Committee Meeting antara Bank Sumut, Konsultan, dan Mitra.',
            ],
            default => !empty($this->recommendation) ? [$this->recommendation] : ['Lampirkan log pengujian dan Berita Acara verifikasi teknis.'],
        };
    }
}
