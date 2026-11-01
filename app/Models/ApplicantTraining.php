<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ApplicantTraining extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const CERTIFICATE = 'certificate';

    protected $fillable = [
        'user_id',
        'training_name',
        'certificate_number',
        'organization',
        'issue_date_ad',
        'issue_date_bs',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::CERTIFICATE)->singleFile();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
