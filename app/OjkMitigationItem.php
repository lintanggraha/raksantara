<?php

namespace App;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class OjkMitigationItem extends Model
{
    use \App\Support\Auditable;

    protected string $auditEvent = 'ojk_mitigation';

    protected $fillable = [
        'item_number',
        'action_plan',
        'pic',
        'solution',
        'target',
        'target_date',
        'mitigation_status',
        'sort_order',
    ];

    protected $casts = [
        'target_date' => 'date',
    ];

    /**
     * Persist target_date as date-only text so SQLite and MySQL store the same value.
     *
     * @param  \Carbon\CarbonInterface|\DateTimeInterface|string|null  $value
     */
    public function setTargetDateAttribute($value): void
    {
        if ($value instanceof CarbonInterface) {
            $this->attributes['target_date'] = $value->toDateString();

            return;
        }

        $text = trim((string) $value);
        $this->attributes['target_date'] = $text === '' ? null : substr($text, 0, 10);
    }
}
