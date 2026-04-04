<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'contact_name',
        'contact_email',
        'status',
        'criticality_level',
    ];

    public function risks()
    {
        return $this->hasMany(Risk::class);
    }
}
