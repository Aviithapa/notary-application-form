<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One status change of an exam application: who moved it, from what, to what, and why. */
class ExamApplicationStatusLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'exam_application_id',
        'from_status',
        'to_status',
        'remarks',
        'changed_by',
    ];

    public function application()
    {
        return $this->belongsTo(ExamApplication::class, 'exam_application_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function toLabel(): string
    {
        return __(ExamApplication::STATUSES[$this->to_status][0] ?? $this->to_status);
    }
}
