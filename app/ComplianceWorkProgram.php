<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceWorkProgram extends Model
{
    use \App\Support\Auditable;

    protected string $auditEvent = 'compliance_program';

    use HasFactory;

    public const SECTION_INTERNAL_AUDIT = 'internal_audit';
    public const SECTION_RISK_MANAGEMENT = 'risk_management';
    public const SECTION_GOVERNANCE_COMPLIANCE = 'governance_compliance';

    protected $fillable = [
        'section',
        'work_id',
        'program_kerja',
        'deskripsi',
        'target_sasaran_kerja',
        'kategori',
        'q1',
        'q2',
        'q3',
        'q4',
        'alignment_champion_program',
        'alignment_risk_profile_2025',
        'support_needed',
        'created_by',
    ];

    protected $casts = [
        'q1' => 'boolean',
        'q2' => 'boolean',
        'q3' => 'boolean',
        'q4' => 'boolean',
    ];

    public static function sectionConfig(): array
    {
        return [
            self::SECTION_INTERNAL_AUDIT => [
                'slug' => 'internal-audit',
                'title' => 'Internal Audit',
                'route_base' => 'compliance.internal-audit',
                'prefix' => 'IA',
            ],
            self::SECTION_RISK_MANAGEMENT => [
                'slug' => 'risk-management',
                'title' => 'Risk Management',
                'route_base' => 'compliance.risk-management',
                'prefix' => 'RM',
            ],
            self::SECTION_GOVERNANCE_COMPLIANCE => [
                'slug' => 'governance-compliance',
                'title' => 'Governance & Compliance',
                'route_base' => 'compliance.compliance',
                'prefix' => 'GC',
            ],
        ];
    }

    public static function configForSection(string $section): array
    {
        $config = self::sectionConfig();

        if (!isset($config[$section])) {
            abort(404);
        }

        return $config[$section];
    }

    public static function nextWorkIdPreview(string $section): string
    {
        $config = self::configForSection($section);
        $prefix = $config['prefix'];
        $year = now()->year;

        $lastWorkId = self::query()
            ->where('section', $section)
            ->where('work_id', 'like', $prefix . '-' . $year . '-%')
            ->orderByDesc('id')
            ->value('work_id');

        $nextNumber = 1;

        if (is_string($lastWorkId) && preg_match('/^' . preg_quote($prefix, '/') . '-' . $year . '-(\d+)$/', $lastWorkId, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        $padLength = max(3, strlen((string) $nextNumber));

        return sprintf('%s-%d-%0' . $padLength . 'd', $prefix, $year, $nextNumber);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
