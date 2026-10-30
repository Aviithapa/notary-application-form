<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medias extends Model
{
    use HasFactory;


    protected $table = 'media';
    protected $appends = ['url'];

    protected $fillable = [
        'name',
        'original_name',
        'mime_type',
        'path',
        'collection',
        'disk',
        'post_id',
        'news_id'
    ];

  
}
