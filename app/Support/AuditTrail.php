<?php

namespace App\Support;

use App\AuditLog;
use App\Risk;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Turns raw audit_logs rows into something a reviewer can read: who did what,
 * when (local time), to which record, and exactly which fields changed.
 */
class AuditTrail
{
    public const DISPLAY_TIMEZONE = 'Asia/Jakarta';
    public const DISPLAY_TIMEZONE_LABEL = 'WIB';

    public const MODULES = [
        'auth' => 'Login & akun',
        'security' => 'Keamanan akun',
        'user' => 'Manajemen user',
        'access' => 'Hak akses',
        'risk' => 'Risk register',
        'risk_appetite' => 'Risk appetite',
        'treatment' => 'Treatment',
        'treatment_template' => 'Template treatment',
        'kri' => 'KRI',
        'incident' => 'Insiden',
        'evidence' => 'Bukti / lampiran',
        'committee' => 'Keputusan komite',
        'bcm' => 'BCM',
        'scenario' => 'Skenario',
        'maturity' => 'Maturity',
        'resilience' => 'Resiliensi operasional',
        'retention' => 'Retensi data',
        'vendor' => 'Vendor',
        'parameter' => 'Parameter',
        'report' => 'Laporan',
        'scheduled_report' => 'Jadwal laporan',
        'compliance_program' => 'Program compliance',
        'ojk_mitigation' => 'Mitigasi OJK',
        'sulteng_internal' => 'Sulteng - Assessment Internal',
        'audit' => 'Audit log',
    ];

    private const EVENTS = [
        'auth.login.succeeded' => 'Login berhasil',
        'auth.login.failed' => 'Login gagal',
        'auth.login.locked' => 'Akun terkunci (terlalu banyak percobaan)',
        'auth.logout' => 'Logout',
        'auth.password.reset' => 'Reset password lewat email',
        'auth.user.registered' => 'Registrasi akun',
        'auth.email.verified' => 'Verifikasi email',
        'security.mfa.enabled' => 'Mengaktifkan MFA',
        'security.mfa.disabled' => 'Menonaktifkan MFA',
        'security.mfa.challenge_passed' => 'Lolos verifikasi MFA',
        'security.mfa.recovery_code_used' => 'Memakai kode pemulihan MFA',
        'security.mfa.recovery_codes_regenerated' => 'Membuat ulang kode pemulihan MFA',
        'security.sso.login' => 'Login lewat SSO',
        'security.sso.linked' => 'Menautkan akun SSO',
        'security.sso.provisioned' => 'Akun dibuat lewat SSO',
        'user.created_by_superadmin' => 'Membuat user',
        'user.password.changed_by_self' => 'Mengganti password sendiri',
        'user.password.reset_by_superadmin' => 'Reset password user',
        'user.profile.updated' => 'Mengubah profil',
        'user.promoted_to_superadmin.via_cli' => 'Menjadikan superadmin (CLI)',
        'access.role.assigned' => 'Memberi role',
        'access.role.revoked' => 'Mencabut role',
        'risk.created' => 'Menambah risk',
        'risk.updated' => 'Mengubah risk',
        'risk.deleted' => 'Menghapus risk',
        'risk.import.completed' => 'Import risk',
        'risk.import.failed' => 'Import risk gagal',
        'risk.status_change.requested' => 'Mengajukan perubahan status risk',
        'risk.status_change.request.updated' => 'Mengubah pengajuan status risk',
        'risk.status_change.approved' => 'Menyetujui perubahan status risk',
        'risk.status_change.rejected' => 'Menolak perubahan status risk',
        'risk.status.changed_by_approval' => 'Status risk berubah (hasil persetujuan)',
        'risk.comment.created' => 'Menulis komentar risk',
        'risk.comment.updated' => 'Mengubah komentar risk',
        'risk.comment.deleted' => 'Menghapus komentar risk',
        'risk.alert.resolved' => 'Menyelesaikan alert risk',
        'evidence.risk.uploaded' => 'Mengunggah bukti risk',
        'evidence.treatment.uploaded' => 'Mengunggah bukti treatment',
        'evidence.comment.uploaded' => 'Mengunggah lampiran komentar',
        'evidence.deleted' => 'Menghapus bukti',
        'evidence.deleted.by_comment_remove' => 'Bukti terhapus (komentar dihapus)',
        'evidence.deleted.by_risk_remove' => 'Bukti terhapus (risk dihapus)',
        'evidence.deleted.by_treatment_remove' => 'Bukti terhapus (treatment dihapus)',
        'kri.value_logged' => 'Mencatat nilai KRI',
        'incident.action.created' => 'Menambah tindakan insiden (CAPA)',
        'incident.learning.reviewed' => 'Review pasca-insiden',
        'bcm.bia.approved' => 'Menyetujui BIA',
        'bcm.bia.saved' => 'Menyimpan BIA',
        'bcm.bcp.saved' => 'Menyimpan BCP',
        'bcm.test.recorded' => 'Mencatat uji BCP',
        'bcm.risk.linked' => 'Menautkan risk ke layanan',
        'bcm.risk.unlinked' => 'Melepas risk dari layanan',
        'report.export.generated' => 'Export laporan',
        'report.export.failed' => 'Export laporan gagal',
        'scheduled_report.run_manually' => 'Menjalankan jadwal laporan manual',
        'retention.purge.requested' => 'Mengajukan penghapusan data',
        'retention.purge.reviewed' => 'Meninjau pengajuan penghapusan data',
        'retention.purge.executed' => 'Menjalankan penghapusan data',
        'ojk_mitigation.imported' => 'Import mitigasi OJK',
        'sulteng_internal.synced' => 'Sinkron temuan dari Excel',
        'audit.exported' => 'Export audit log',
    ];

    private const OBJECTS = [
        'risk' => 'risk', 'kri' => 'KRI', 'incident' => 'insiden', 'treatment' => 'treatment',
        'treatment_template' => 'template treatment', 'vendor' => 'vendor', 'scheduled_report' => 'jadwal laporan',
        'compliance_program' => 'program compliance', 'ojk_mitigation' => 'item mitigasi OJK', 'risk_appetite' => 'risk appetite',
        'parameter.impact_category' => 'kategori dampak', 'parameter.impact_scale' => 'skala dampak',
        'parameter.probability_scale' => 'skala kemungkinan', 'parameter.radar_group' => 'grup radar',
        'parameter.risk_category' => 'kategori risk', 'parameter.cause_category' => 'kategori penyebab',
        'parameter.risk_owner' => 'divisi pemilik risk', 'sulteng_internal.finding' => 'temuan Sulteng',
        'committee.decision' => 'keputusan komite', 'bcm.service' => 'layanan BCM', 'vendor.assessment' => 'penilaian vendor',
        'scenario' => 'skenario', 'retention.schedule' => 'jadwal retensi',
    ];

    private const VERBS = [
        'created' => 'Menambah', 'updated' => 'Mengubah', 'deleted' => 'Menghapus', 'saved' => 'Menyimpan',
        'approved' => 'Menyetujui', 'rejected' => 'Menolak', 'requested' => 'Mengajukan', 'reviewed' => 'Meninjau',
        'recorded' => 'Mencatat', 'verified' => 'Memverifikasi', 'exported' => 'Export', 'imported' => 'Import',
        'resolved' => 'Menyelesaikan', 'uploaded' => 'Mengunggah',
    ];

    /** Columns that hold a user id; shown as the user's name. */
    private const USER_FIELDS = '/(^|_)(by|user_id|actor_id|owner_user_id|approver_id|reviewer_id|requester_id)$/';

    private const FIELD_LABELS = [
        'requested_by' => 'Diajukan oleh', 'reviewed_by' => 'Ditinjau oleh', 'approved_by' => 'Disetujui oleh',
        'reported_by' => 'Dilaporkan oleh', 'created_by' => 'Dibuat oleh', 'updated_by' => 'Diubah oleh',
        'last_updated_by' => 'Terakhir diubah oleh', 'assessed_by' => 'Dinilai oleh', 'uploaded_by' => 'Diunggah oleh',
        'user_id' => 'User', 'reviewed_at' => 'Waktu ditinjau', 'approved_at' => 'Waktu disetujui',
        'review_note' => 'Catatan peninjau', 'from_status' => 'Status awal', 'to_status' => 'Status tujuan',
        'risk_id' => 'Risk', 'risk_title' => 'Judul risk', 'risk_owner' => 'Divisi pemilik', 'status' => 'Status',
        'affected_client' => 'Client terdampak', 'severity' => 'Tingkat keparahan', 'loss_amount' => 'Nilai kerugian',
        'title' => 'Judul', 'name' => 'Nama', 'description' => 'Deskripsi', 'deadline' => 'Tenggat', 'pic' => 'PIC',
        'is_active' => 'Aktif', 'recipients' => 'Penerima', 'reason' => 'Alasan', 'content' => 'Isi',
    ];

    public static function moduleOf(string $event): string
    {
        return explode('.', $event, 2)[0];
    }

    public static function moduleLabel(string $module): string
    {
        return self::MODULES[$module] ?? Str::headline($module);
    }

    public static function eventLabel(string $event): string
    {
        if (isset(self::EVENTS[$event])) {
            return self::EVENTS[$event];
        }

        $parts = explode('.', $event);
        $action = array_pop($parts);
        $object = implode('.', $parts);
        if (isset(self::VERBS[$action])) {
            return self::VERBS[$action] . ' ' . (self::OBJECTS[$object] ?? str_replace(['.', '_'], ' ', $object));
        }

        return Str::headline(str_replace('.', ' ', $event));
    }

    public static function fieldLabel(string $field): string
    {
        return self::FIELD_LABELS[$field] ?? Str::ucfirst(str_replace('_', ' ', $field));
    }

    public static function localTime($time): ?CarbonImmutable
    {
        return $time ? CarbonImmutable::parse($time)->setTimezone(self::DISPLAY_TIMEZONE)->locale('id') : null;
    }

    /**
     * @param  Collection<int, AuditLog>  $logs
     * @return array<int, array> one presentable row per log, in the same order
     */
    public static function present(Collection $logs): array
    {
        $users = self::resolveUsers($logs);
        $risks = self::resolveRisks($logs);

        return $logs->map(fn (AuditLog $log) => self::presentOne($log, $users, $risks))->all();
    }

    private static function presentOne(AuditLog $log, Collection $users, Collection $risks): array
    {
        $before = is_array($log->before_state) ? $log->before_state : [];
        $after = is_array($log->after_state) ? $log->after_state : [];
        $event = (string) $log->event;

        $changes = [];
        $keys = array_values(array_unique(array_merge(array_keys($before), array_keys($after))));
        foreach ($keys as $key) {
            if (in_array($key, ['created_at', 'updated_at', 'id'], true)) {
                continue;
            }
            $old = $before[$key] ?? null;
            $new = $after[$key] ?? null;
            $hasBoth = $before !== [] && $after !== [];
            if ($hasBoth && self::same($old, $new)) {
                continue; // identity field carried on both sides, not a change
            }
            $changes[] = [
                'field' => self::fieldLabel((string) $key),
                'before' => array_key_exists($key, $before) ? self::display((string) $key, $old, $users, $risks, $log) : null,
                'after' => array_key_exists($key, $after) ? self::display((string) $key, $new, $users, $risks, $log) : null,
            ];
        }

        $actor = $log->actor;
        $time = self::localTime($log->created_at);
        $state = $after + $before;

        return [
            'id' => $log->id,
            'event' => $event,
            'activity' => self::eventLabel($event),
            'module' => self::moduleLabel(self::moduleOf($event)),
            'day' => $time ? $time->translatedFormat('l, d M Y') : '-',
            'clock' => $time ? $time->format('H:i:s') . ' ' . self::DISPLAY_TIMEZONE_LABEL : '',
            'timestamp' => $time ? $time->format('Y-m-d H:i:s') : '',
            'actor' => $actor ? $actor->name : (self::attemptedIdentity($state) ?: 'Sistem'),
            'actor_detail' => $actor
                ? trim('@' . $actor->username . ($actor->division ? ' · ' . RiskOwnerAccess::displayLabel($actor->division, $actor->division) : ''))
                : ($log->actor_id ? 'User #' . $log->actor_id . ' (sudah dihapus)' : (self::attemptedIdentity($state) ? 'percobaan login' : 'proses otomatis')),
            'object' => self::objectLabel($log, $state, $risks),
            'approval' => self::approvalSummary($state, $users),
            'changes' => $changes,
            'change_kind' => $before !== [] && $after !== [] ? 'updated' : ($after !== [] ? 'created' : ($before !== [] ? 'deleted' : 'none')),
            'ip' => (string) $log->ip_address,
            'user_agent' => (string) $log->user_agent,
            'browser' => self::browser((string) $log->user_agent),
        ];
    }

    private static function same($a, $b): bool
    {
        return json_encode($a) === json_encode($b) || (is_scalar($a) && is_scalar($b) && (string) $a === (string) $b);
    }

    private static function display(string $key, $value, Collection $users, Collection $risks, AuditLog $log): string
    {
        if ($value === null || $value === '') {
            return '(kosong)';
        }
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }
        if (is_numeric($value) && preg_match(self::USER_FIELDS, $key)) {
            $user = $users->get((int) $value);

            return $user ? $user->name . ' (@' . $user->username . ')' : 'User #' . $value;
        }
        if ($key === 'risk_id' && is_numeric($value) && $log->auditable_type !== Risk::class) {
            $risk = $risks->get((int) $value);

            return $risk ? $risk->risk_id . ' · ' . Str::limit((string) $risk->risk_title, 60) : 'Risk #' . $value;
        }
        if (is_array($value)) {
            return Str::limit(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 500);
        }
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
            $time = self::localTime($value);

            return $time->format('d M Y H:i:s') . ' ' . self::DISPLAY_TIMEZONE_LABEL;
        }

        return Str::limit((string) $value, 1000);
    }

    private static function objectLabel(AuditLog $log, array $state, Collection $risks): string
    {
        $type = class_basename((string) $log->auditable_type);
        if ($type === 'User' && str_starts_with((string) $log->event, 'auth.')) {
            return '';
        }

        foreach (['incident_code', 'code', 'item_number', 'finding_number', 'name', 'title', 'metric_name', 'label', 'level'] as $key) {
            if (!empty($state[$key]) && is_scalar($state[$key])) {
                $identifier = (string) $state[$key];
                break;
            }
        }
        if ($log->auditable_type === Risk::class) {
            $identifier = trim(($state['risk_id'] ?? '') . ' · ' . Str::limit((string) ($state['risk_title'] ?? ''), 60), ' ·');
        } elseif (isset($state['risk_id']) && is_numeric($state['risk_id']) && ($risk = $risks->get((int) $state['risk_id']))) {
            $identifier = trim(($identifier ?? '') . ' (risk ' . $risk->risk_id . ')');
        }

        $label = Str::headline($type);
        $id = $log->auditable_id ? ' #' . $log->auditable_id : '';

        return trim($label . $id . (!empty($identifier) ? ' — ' . Str::limit($identifier, 90) : ''));
    }

    /**
     * "Diajukan oleh A · Disetujui oleh B" for events that carry both sides.
     */
    private static function approvalSummary(array $state, Collection $users): ?string
    {
        $parts = [];
        foreach (['requested_by' => 'Diajukan oleh', 'assessed_by' => 'Dinilai oleh', 'reviewed_by' => 'Ditinjau oleh', 'approved_by' => 'Disetujui oleh'] as $key => $label) {
            if (!empty($state[$key]) && is_numeric($state[$key])) {
                $user = $users->get((int) $state[$key]);
                $parts[] = $label . ' ' . ($user ? $user->name : 'User #' . $state[$key]);
            }
        }

        return count($parts) > 0 ? implode(' · ', $parts) : null;
    }

    private static function attemptedIdentity(array $state): ?string
    {
        foreach (['login', 'username', 'email', 'identifier'] as $key) {
            if (!empty($state[$key]) && is_string($state[$key])) {
                return $state[$key];
            }
        }

        return null;
    }

    private static function browser(string $agent): string
    {
        foreach (['Edg/' => 'Edge', 'OPR/' => 'Opera', 'Firefox/' => 'Firefox', 'Chrome/' => 'Chrome', 'Safari/' => 'Safari'] as $needle => $name) {
            if (str_contains($agent, $needle)) {
                $os = str_contains($agent, 'Windows') ? 'Windows' : (str_contains($agent, 'Android') ? 'Android' : (str_contains($agent, 'iPhone') || str_contains($agent, 'iPad') ? 'iOS' : (str_contains($agent, 'Mac OS') ? 'macOS' : (str_contains($agent, 'Linux') ? 'Linux' : ''))));

                return trim($name . ' ' . ($os ? '· ' . $os : ''));
            }
        }

        return $agent === '' ? '' : Str::limit($agent, 30);
    }

    private static function resolveUsers(Collection $logs): Collection
    {
        $ids = [];
        foreach ($logs as $log) {
            foreach ([(array) $log->before_state, (array) $log->after_state] as $state) {
                foreach ($state as $key => $value) {
                    if (is_numeric($value) && preg_match(self::USER_FIELDS, (string) $key)) {
                        $ids[] = (int) $value;
                    }
                }
            }
        }

        return $ids === [] ? collect() : User::query()->whereIn('id', array_unique($ids))->get(['id', 'name', 'username'])->keyBy('id');
    }

    private static function resolveRisks(Collection $logs): Collection
    {
        $ids = [];
        foreach ($logs as $log) {
            if ($log->auditable_type === Risk::class) {
                continue; // a Risk's own risk_id is its code, not a key
            }
            foreach ([(array) $log->before_state, (array) $log->after_state] as $state) {
                if (isset($state['risk_id']) && is_numeric($state['risk_id'])) {
                    $ids[] = (int) $state['risk_id'];
                }
            }
        }

        return $ids === [] ? collect() : Risk::query()->whereIn('id', array_unique($ids))->get(['id', 'risk_id', 'risk_title'])->keyBy('id');
    }
}
