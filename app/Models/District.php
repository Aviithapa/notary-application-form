<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'province_id',
        'name_en',
        'name_np',
        'area_sq_km_en',
        'area_sq_km_np',
        'website',
        'headquarter_en',
        'headquarter_np',
    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }

}
