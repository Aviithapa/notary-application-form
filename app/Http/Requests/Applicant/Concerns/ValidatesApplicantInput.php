<?php

namespace App\Http\Requests\Applicant\Concerns;

use App\Support\NepaliDate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesApplicantInput
{
    /** Uploaded images/scans: kept within the default PHP upload_max_filesize of 2 MB. */
    protected const MAX_UPLOAD_KB = 2048;

    protected const DOCUMENT_MIMES = 'mimes:jpg,jpeg,png,webp,pdf';

    /**
     * District -> municipality -> ward, where each level must belong to the one above it.
     * $prefix is '' for the permanent address or e.g. 'correspondence_'.
     */
    protected function addressRules(string $prefix = ''): array
    {
        return [
            "{$prefix}district_id" => ['nullable', 'integer', Rule::exists('districts', 'id')],
            "{$prefix}municipality_id" => ['nullable', 'integer',
                Rule::exists('municipalities', 'id')->where('district_id', $this->input("{$prefix}district_id"))],
            "{$prefix}ward_id" => ['nullable', 'integer',
                Rule::exists('wards', 'id')->where('municipality_id', $this->input("{$prefix}municipality_id"))],
            "{$prefix}tole" => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function documentRules(): array
    {
        return ['nullable', 'file', self::DOCUMENT_MIMES, 'max:' . self::MAX_UPLOAD_KB];
    }

    /**
     * Checks that an AD/BS date pair converts and agrees; reports the error on the bad field.
     */
    protected function validateDatePair(Validator $validator, string $adKey, string $bsKey): void
    {
        if ($validator->errors()->hasAny([$adKey, $bsKey])) {
            return;
        }

        [, , $errorField] = NepaliDate::reconcile($this->input($adKey), $this->input($bsKey));

        if ($errorField === 'ad') {
            $validator->errors()->add($adKey, __('This date cannot be converted to B.S.'));
        } elseif ($errorField === 'bs') {
            $validator->errors()->add($bsKey, __('Enter a valid B.S. date (YYYY-MM-DD) that matches the A.D. date.'));
        }
    }
}
