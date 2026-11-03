<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ApplicantProfile extends Model implements HasMedia
{
    use HasLocalizedFields, InteractsWithMedia;

    public const PHOTO = 'photo';
    public const CITIZENSHIP_FRONT = 'citizenship_front';
    public const CITIZENSHIP_BACK = 'citizenship_back';

    protected $fillable = [
        'user_id',
        'name',
        'name_np',
        'district_id',
        'municipality_id',
        'ward_id',
        'tole',
        'dob_ad',
        'dob_bs',
        'citizenship_number',
        'citizenship_issue_district_id',
        'occupation',
        'gender',
        'religion',
        'mother_tongue',
        'caste',
        'huliya',
        'mother_name',
        'mother_name_np',
        'father_name',
        'father_name_np',
        'grandfather_name',
        'grandfather_name_np',
        'correspondence_district_id',
        'correspondence_municipality_id',
        'correspondence_ward_id',
        'correspondence_tole',
        'fax',
        'phone',
        'email',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::PHOTO)->singleFile();
        $this->addMediaCollection(self::CITIZENSHIP_FRONT)->singleFile();
        $this->addMediaCollection(self::CITIZENSHIP_BACK)->singleFile();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function citizenshipIssueDistrict()
    {
        return $this->belongsTo(District::class, 'citizenship_issue_district_id');
    }

    public function correspondenceDistrict()
    {
        return $this->belongsTo(District::class, 'correspondence_district_id');
    }

    public function correspondenceMunicipality()
    {
        return $this->belongsTo(Municipality::class, 'correspondence_municipality_id');
    }

    public function correspondenceWard()
    {
        return $this->belongsTo(Ward::class, 'correspondence_ward_id');
    }
}
