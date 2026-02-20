<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImpactCategory extends Model
{
    protected $fillable = ['name'];

    public function scales()
    {
        return $this->hasMany(ImpactScale::class);
    }
}
