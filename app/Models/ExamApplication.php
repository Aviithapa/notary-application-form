<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An applicant's application to an exam (application call). */
class ExamApplication extends Model
{
    protected $fillable = [
        'exam_id',
        'user_id',
        'application_number',
        'fee_amount',
        'is_double_fee',
        'status',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
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

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
