<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResilienceBackupRecord extends Model
{
    use HasFactory;

    public const ASSET_SCOPES = ['database', 'files', 'configuration', 'full_system'];
    public const STATUSES = ['successful', 'partial', 'failed'];
    public const ENCRYPTION_STATUSES = ['encrypted', 'not_encrypted', 'unknown'];

    protected $fillable = [
        'backup_reference',
        'asset_scope',
        'environment',
        'storage_location',
        'encryption_status',
        'status',
        'started_at',
        'completed_at',
        'checksum',
        'retention_until',
        'verified_at',
        'evidence_reference',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'retention_until' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function restoreTests()
    {
        return $this->hasMany(ResilienceRestoreTest::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
