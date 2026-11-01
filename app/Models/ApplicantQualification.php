<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ApplicantQualification extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const DOCUMENTS = 'documents';

    protected $fillable = [
        'user_id',
        'university_name',
        'passed_year',
        'level',
        'division',
        'percentage',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::DOCUMENTS);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
