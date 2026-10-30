<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    protected $fillable = [
        'district_id',
        'category_id',
        'name_en',
        'name_np',
        'area_sq_km_en',
        'area_sq_km_np',
        'website',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function wards()
    {
        return $this->hasMany(Ward::class);
    }
}
