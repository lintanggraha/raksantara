<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImpactScale extends Model
{
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
