<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

class Risk extends Model
{
    use HasFactory;
    protected $fillable = [
    'risk_id',
    'year',
    'risk_title',
    'risk_category_id',
    'risk_appetite_id',
    'risk_cause_category_id',
    'cause',
    'existing_control',
    'risk_owner',
    'created_by',
    'sasaran_target',
    'risk_description',
    'probability_notes',
    'probability_score',
    'impact_scale_id',
    'impact_notes',
    'impact_score',
    'risk_score',
    'status',
    'vendor_id'
];

    public function category()
    {
        return $this->belongsTo(RiskCategory::class, 'risk_category_id');
    }

    public function causeCategory()
    {
        return $this->belongsTo(RiskCauseCategory::class, 'risk_cause_category_id');
    }

    public function getRiskLevelAttribute()
    {
        $score = $this->risk_score;

        if (is_null($score)) return null;

        if ($score <= 5) return 'Low';
        if ($score <= 11) return 'Low to Moderate';
        if ($score <= 15) return 'Moderate';
        if ($score <= 19) return 'Moderate to High';

        return 'High';
    }

    public function impactScale()
    {
        return $this->belongsTo(ImpactScale::class);
    }

    public static function generateRiskId(?int $year = null): string
    {
        $year = $year ?: Carbon::now()->year;
        $prefix = 'RISK-' . $year . '-';
        $maxNumber = 0;
        $driver = self::query()->getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $maxNumber = (int) (self::query()
                ->where('year', $year)
                ->where('risk_id', 'like', $prefix . '%')
                ->selectRaw("MAX(CAST(SUBSTRING_INDEX(risk_id, '-', -1) AS UNSIGNED)) as max_number")
                ->value('max_number') ?? 0);

            return sprintf('RISK-%d-%03d', $year, $maxNumber + 1);
        }

        foreach (self::query()->where('year', $year)->whereNotNull('risk_id')->pluck('risk_id') as $riskId) {
            if (preg_match('/^RISK\-' . preg_quote((string) $year, '/') . '\-(\d+)$/', (string) $riskId, $matches) !== 1) {
                continue;
            }

            $maxNumber = max($maxNumber, (int) $matches[1]);
        }

        return sprintf('RISK-%d-%03d', $year, $maxNumber + 1);
    }

    public static function createWithGeneratedRiskId(array $attributes, int $maxAttempts = 5): self
    {
        $year = (int) ($attributes['year'] ?? Carbon::now()->year);
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;
            $attributes['year'] = $year;
            $attributes['risk_id'] = self::generateRiskId($year);

            try {
                return self::create($attributes);
            } catch (QueryException $exception) {
                if (!self::isRiskIdDuplicateException($exception) || $attempt >= $maxAttempts) {
                    throw $exception;
                }
            }
        }

        throw new \RuntimeException('Unable to generate unique risk ID.');
    }

    private static function isRiskIdDuplicateException(QueryException $exception): bool
    {
        $message = strtolower((string) $exception->getMessage());

        return strpos($message, 'duplicate') !== false
            && strpos($message, 'risk') !== false
            && strpos($message, 'id') !== false;
    }
    
    public function treatments()
    {
        return $this->hasMany(\App\RiskTreatment::class);
    }

    public function inherentAnalysis()
    {
        return $this->hasOne(RiskInherentAnalysis::class);
    }

    public function residualAnalysis()
    {
        return $this->hasOne(RiskResidualAnalysis::class);
    }

    public function statusChangeRequests()
    {
        return $this->hasMany(RiskStatusChangeRequest::class, 'risk_id');
    }

    public function riskAppetite()
    {
        return $this->belongsTo(RiskAppetite::class, 'risk_appetite_id', 'id');
    }

    public function alerts()
    {
        return $this->hasMany(RiskAlert::class);
    }

    public function attachments()
    {
        return $this->morphMany(EvidenceAttachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->hasMany(RiskComment::class);
    }

    public function incidents()
    {
        return $this->hasMany(RiskIncident::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kris()
    {
        return $this->hasMany(Kri::class);
    }

    public function rcsaWorkspace()
    {
        return $this->hasOne(RiskRcsaWorkspace::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function bia()
    {
        return $this->hasOne(Bia::class);
    }

}
