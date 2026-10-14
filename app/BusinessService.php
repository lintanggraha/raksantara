<?php

namespace App;

use App\Support\BusinessContinuity;
use Illuminate\Database\Eloquent\Model;

/**
 * A business service or process that the organisation must keep running.
 * Business continuity (BIA, BCP, tests) is managed per service.
 */
class BusinessService extends Model
{
    public const STATUSES = [
        'draft' => 'Draf',
        'active' => 'Aktif',
        'retired' => 'Tidak aktif',
    ];

    protected $fillable = [
        'code',
        'name',
        'owner_unit',
        'description',
        'status',
        'dependency_systems',
        'dependency_vendors',
        'dependency_sites',
        'key_personnel',
        'created_by',
        'updated_by',
    ];

    public function bia()
    {
        return $this->hasOne(ServiceBia::class);
    }

    public function bcp()
    {
        return $this->hasOne(ServiceBcp::class);
    }

    public function tests()
    {
        return $this->hasMany(ServiceBcpTest::class)->orderByDesc('tested_at')->orderByDesc('id');
    }

    public function latestTest()
    {
        return $this->hasOne(ServiceBcpTest::class)->latestOfMany('tested_at');
    }

    public function risks()
    {
        return $this->belongsToMany(Risk::class, 'business_service_risk')->withTimestamps();
    }

    /**
     * Where the service stands in the continuity cycle, for badges and filters.
     */
    public function continuityStatus(): string
    {
        if (!$this->bia) {
            return 'no_bia';
        }
        if (!$this->bia->approved_at) {
            return 'bia_pending_approval';
        }
        if (!$this->bcp) {
            return 'no_bcp';
        }
        $latest = $this->latestTest;
        if (!$latest) {
            return 'never_tested';
        }
        $due = BusinessContinuity::nextTestDue($this->bcp->testing_frequency, $latest->tested_at);
        if ($due && $due->isPast()) {
            return 'test_overdue';
        }
        if ($latest->result === 'fail') {
            return 'last_test_failed';
        }

        return 'ok';
    }

    public static function continuityStatusLabels(): array
    {
        return [
            'no_bia' => ['BIA belum diisi', 'bg-secondary-lt'],
            'bia_pending_approval' => ['BIA menunggu persetujuan', 'bg-yellow-lt'],
            'no_bcp' => ['BCP belum disusun', 'bg-orange-lt'],
            'never_tested' => ['BCP belum pernah diuji', 'bg-orange-lt'],
            'test_overdue' => ['Uji BCP terlambat', 'bg-red-lt'],
            'last_test_failed' => ['Uji terakhir gagal', 'bg-red-lt'],
            'ok' => ['Siap', 'bg-green-lt'],
        ];
    }
}
