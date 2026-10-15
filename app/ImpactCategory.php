<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImpactCategory extends Model
{
    use \App\Support\Auditable;

    protected string $auditEvent = 'parameter.impact_category';

    protected $fillable = ['name'];

    public function scales()
    {
        return $this->hasMany(ImpactScale::class);
    }
}
