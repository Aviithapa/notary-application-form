<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $fillable = [
        'municipality_id',
        'ward_number'
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
}
