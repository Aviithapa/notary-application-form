<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/** An applicant's application to an exam (application call). */
class ExamApplication extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const VOUCHER = 'voucher';
    public const SIGNATURE = 'signature';
    public const THUMB_LEFT = 'thumb_left';
    public const THUMB_RIGHT = 'thumb_right';

    /** Signature and thumbprints, collection => English label. */
    public const IDENTITY_MARKS = [
        self::SIGNATURE => 'Signature',
        self::THUMB_LEFT => 'Left thumbprint',
        self::THUMB_RIGHT => 'Right thumbprint',
    ];

    protected $fillable = [
        'exam_id',
        'user_id',
        'application_number',
        'fee_amount',
        'is_double_fee',
        'paid_amount',
        'status',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'is_double_fee' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Readable reference once the id is known, e.g. NPC-2026-000042
        static::created(function (ExamApplication $application) {
            $application->forceFill([
                'application_number' => sprintf('NPC-%s-%06d', $application->created_at->format('Y'), $application->id),
            ])->saveQuietly();
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::VOUCHER)->singleFile();
        foreach (array_keys(self::IDENTITY_MARKS) as $collection) {
            $this->addMediaCollection($collection)->singleFile();
        }
    }

    /** Loads everything the "view application" page shows: exam, uploads and the applicant's form. */
    public function loadFullDetails(): static
    {
        $this->load(['exam.service', 'media', 'user']);
        $this->user->loadApplicationForm();

        return $this;
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
