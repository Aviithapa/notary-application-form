<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = [
        'name_en',
        'name_np',
        'area_sq_km_en',
        'area_sq_km_np',
        'website',
        'headquarter_en',
        'headquarter_np',
    ];

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
