<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResilienceRestoreTest extends Model
{
    use HasFactory;

    public const TEST_TYPES = ['tabletop', 'partial_restore', 'full_restore', 'ransomware_recovery'];
    public const RESULTS = ['passed', 'passed_with_findings', 'failed'];

    protected $fillable = [
        'resilience_backup_record_id',
        'test_type',
        'performed_at',
        'recovery_time_minutes',
        'recovery_point_minutes',
        'result',
        'findings',
        'remediation_plan',
        'evidence_reference',
        'performed_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function backupRecord()
    {
        return $this->belongsTo(ResilienceBackupRecord::class, 'resilience_backup_record_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
