<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImpactScale extends Model
{
    use \App\Support\Auditable;

    protected string $auditEvent = 'parameter.impact_scale';

    protected $fillable = [
        'impact_category_id',
        'description',
        'score'
    ];

    public function category()
    {
        return $this->belongsTo(ImpactCategory::class);
    }
}
