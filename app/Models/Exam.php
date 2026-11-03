<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * An exam is opened as an application call for a service. Applications are accepted from
 * application_start to application_end at the service fee, then until double_fee_end at twice
 * the fee (दोब्बर दस्तुर). Dates are Y-m-d strings; the *_ad columns are used for comparisons.
 */
class Exam extends Model
{
    use HasLocalizedFields;

    /** Status key => English label (translated in the views). */
    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const PHASE_UPCOMING = 'upcoming';
    public const PHASE_OPEN = 'open';
    public const PHASE_DOUBLE_FEE = 'double_fee';
    public const PHASE_CLOSED = 'closed';

    protected $fillable = [
        'service_id',
        'title',
        'title_np',
        'application_start_ad',
        'application_start_bs',
        'application_end_ad',
        'application_end_bs',
        'double_fee_end_ad',
        'double_fee_end_bs',
        'exam_date_ad',
        'exam_date_bs',
        'start_time',
        'duration_minutes',
        'venue',
        'venue_np',
        'full_marks',
        'pass_marks',
        'status',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function applications()
    {
        return $this->hasMany(ExamApplication::class);
    }

    public function statusLabel(): string
    {
        return __(self::STATUSES[$this->status] ?? $this->status);
    }

    /** Today's date (Y-m-d) in Nepal time, so deadlines close at local midnight. */
    public static function today(): string
    {
        return now(config('app.business_timezone'))->toDateString();
    }

    /** Last day applications are accepted: the double-fee date if set, else the normal deadline. */
    public function lastApplicationDate(): ?string
    {
        return $this->double_fee_end_ad ?: $this->application_end_ad;
    }

    public function phase(?string $today = null): string
    {
        $today ??= self::today();

        if ($this->status !== 'published' || ! $this->application_start_ad || ! $this->application_end_ad) {
            return self::PHASE_CLOSED;
        }
        if ($today < $this->application_start_ad) {
            return self::PHASE_UPCOMING;
        }
        if ($today <= $this->application_end_ad) {
            return self::PHASE_OPEN;
        }
        if ($this->double_fee_end_ad && $today <= $this->double_fee_end_ad) {
            return self::PHASE_DOUBLE_FEE;
        }

        return self::PHASE_CLOSED;
    }

    public function isAcceptingApplications(?string $today = null): bool
    {
        return in_array($this->phase($today), [self::PHASE_OPEN, self::PHASE_DOUBLE_FEE], true);
    }

    /** Fee payable if applying today; null when the service is free. */
    public function currentFee(?string $today = null): ?string
    {
        if (! $this->service->is_fee_based) {
            return null;
        }

        $multiplier = $this->phase($today) === self::PHASE_DOUBLE_FEE ? 2 : 1;

        return number_format((float) $this->service->fee_amount * $multiplier, 2, '.', '');
    }

    /** Published calls whose application window includes today. */
    public function scopeAcceptingApplications(Builder $query, ?string $today = null): Builder
    {
        $today ??= self::today();

        return $query->where('status', 'published')
            ->where('application_start_ad', '<=', $today)
            ->whereRaw('COALESCE(double_fee_end_ad, application_end_ad) >= ?', [$today]);
    }
}
