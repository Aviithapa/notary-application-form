<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasLocalizedFields;

    /** Purpose key => English label (translated in the views). */
    public const PURPOSES = [
        'new_license' => 'New notary licence',
        'renewal' => 'Licence renewal',
        'examination' => 'Examination',
        'training' => 'Training',
        'certificate' => 'Certificate / recommendation',
        'other' => 'Other',
    ];

    /** Icons the admin can pick for a service card (Bootstrap Icons). */
    public const ICONS = [
        'bi-briefcase', 'bi-award', 'bi-arrow-repeat', 'bi-pencil-square', 'bi-journal-text',
        'bi-patch-check', 'bi-file-earmark-text', 'bi-mortarboard', 'bi-bank', 'bi-person-badge',
    ];

    protected $fillable = [
        'name',
        'name_np',
        'description',
        'description_np',
        'purpose',
        'icon',
        'is_fee_based',
        'fee_amount',
        'requires_exam',
        'requires_application_form',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_fee_based' => 'boolean',
        'fee_amount' => 'decimal:2',
        'requires_exam' => 'boolean',
        'requires_application_form' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function applications()
    {
        return $this->hasManyThrough(ExamApplication::class, Exam::class);
    }

    public function purposeLabel(): string
    {
        return __(self::PURPOSES[$this->purpose] ?? $this->purpose);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
