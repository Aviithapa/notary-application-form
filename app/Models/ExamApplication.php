<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * An applicant's application to an exam (application call). Status workflow:
 * pending -> approved -> ready for admit card -> admit card generated -> passed / failed,
 * with rejected possible before the admit card. Every change is logged in statusLogs.
 */
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

    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const READY_FOR_ADMIT_CARD = 'ready_for_admit_card';
    public const ADMIT_CARD_GENERATED = 'admit_card_generated';
    public const PASSED = 'passed';
    public const FAILED = 'failed';
    public const REJECTED = 'rejected';

    /** Status => [English label, badge classes], in workflow order. */
    public const STATUSES = [
        self::PENDING => ['Pending', 'bg-warning-subtle text-warning'],
        self::APPROVED => ['Approved', 'bg-info-subtle text-info'],
        self::READY_FOR_ADMIT_CARD => ['Ready for admit card', 'bg-primary-subtle text-primary'],
        self::ADMIT_CARD_GENERATED => ['Admit card generated', 'bg-dark-subtle text-dark'],
        self::PASSED => ['Passed', 'bg-success-subtle text-success'],
        self::FAILED => ['Failed', 'bg-danger-subtle text-danger'],
        self::REJECTED => ['Rejected', 'bg-danger text-white'],
    ];

    /** Status => statuses an admin can move it to. */
    public const TRANSITIONS = [
        self::PENDING => [self::APPROVED, self::REJECTED],
        self::APPROVED => [self::READY_FOR_ADMIT_CARD, self::REJECTED],
        self::READY_FOR_ADMIT_CARD => [self::ADMIT_CARD_GENERATED, self::REJECTED],
        self::ADMIT_CARD_GENERATED => [self::PASSED, self::FAILED],
        // Corrections after results are published, and reopening a rejected application
        self::PASSED => [self::FAILED],
        self::FAILED => [self::PASSED],
        self::REJECTED => [self::PENDING],
    ];

    /** While every application is in one of these, the applicant may still edit their form. */
    public const PROFILE_EDITABLE_STATUSES = [self::PENDING, self::REJECTED];

    /** Statuses in which the admit card exists and can be printed. */
    public const ADMIT_CARD_STATUSES = [self::ADMIT_CARD_GENERATED, self::PASSED, self::FAILED];

    protected $attributes = ['status' => self::PENDING];

    protected $fillable = [
        'exam_id',
        'user_id',
        'application_number',
        'fee_amount',
        'is_double_fee',
        'paid_amount',
        'status',
        'remarks',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'is_double_fee' => 'boolean',
        'admit_card_generated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Readable reference once the id is known, e.g. NPC-2026-000042
        static::created(function (ExamApplication $application) {
            $application->forceFill([
                'application_number' => sprintf('NPC-%s-%06d', $application->created_at->format('Y'), $application->id),
            ])->saveQuietly();

            // First entry of the history: the applicant submitting it
            $application->statusLogs()->create([
                'from_status' => null,
                'to_status' => $application->status,
                'changed_by' => $application->user_id,
            ]);
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

    public function statusLabel(): string
    {
        return __(self::STATUSES[$this->status][0] ?? $this->status);
    }

    public function statusClass(): string
    {
        return self::STATUSES[$this->status][1] ?? 'bg-light text-dark';
    }

    /** @return string[] statuses this application can move to next */
    public function nextStatuses(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function canMoveTo(string $status): bool
    {
        return in_array($status, $this->nextStatuses(), true);
    }

    public function hasAdmitCard(): bool
    {
        return in_array($this->status, self::ADMIT_CARD_STATUSES, true);
    }

    /** Roll number as printed on the admit card, e.g. 00042. */
    public function rollNumberLabel(): ?string
    {
        return $this->roll_number ? str_pad((string) $this->roll_number, 5, '0', STR_PAD_LEFT) : null;
    }

    /**
     * Moves the application to $status and logs it. Generating the admit card assigns the next
     * roll number for the exam. Rejection needs remarks (the reason shown to the applicant).
     *
     * @throws DomainException when the move is not allowed from the current status
     */
    public function changeStatus(string $status, ?string $remarks, ?User $by): void
    {
        if (! $this->canMoveTo($status)) {
            throw new DomainException(__('Cannot change status from :from to :to.', [
                'from' => $this->statusLabel(),
                'to' => __(self::STATUSES[$status][0] ?? $status),
            ]));
        }
        if ($status === self::REJECTED && blank($remarks)) {
            throw new DomainException(__('Give a reason for rejecting the application.'));
        }

        DB::transaction(function () use ($status, $remarks, $by) {
            $from = $this->status;

            if ($status === self::ADMIT_CARD_GENERATED && ! $this->roll_number) {
                // Lock the exam row so two admins generating cards at once get different numbers
                Exam::whereKey($this->exam_id)->lockForUpdate()->first();
                $this->roll_number = (int) static::where('exam_id', $this->exam_id)->max('roll_number') + 1;
                $this->admit_card_generated_at = now();
            }

            $this->status = $status;
            $this->remarks = $remarks;
            $this->save();

            $this->statusLogs()->create([
                'from_status' => $from,
                'to_status' => $status,
                'remarks' => $remarks,
                'changed_by' => $by?->id,
            ]);
        });
    }

    public function statusLogs()
    {
        return $this->hasMany(ExamApplicationStatusLog::class)->latest('id');
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
