<?php

namespace App\Http\Requests\Applicant;

use App\Http\Requests\Applicant\Concerns\ValidatesApplicantInput;
use App\Support\NepaliDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PersonalDetailsRequest extends FormRequest
{
    use ValidatesApplicantInput;

    public const GENDERS = ['male', 'female', 'other'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $text = ['nullable', 'string', 'max:255'];

        return [
            'name' => $text,
            'name_np' => $text,
            ...$this->addressRules(),

            'dob_ad' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'dob_bs' => ['nullable', 'string', 'max:10'],

            'citizenship_number' => $text,
            'citizenship_issue_district_id' => ['nullable', 'integer', Rule::exists('districts', 'id')],

            'occupation' => $text,
            'gender' => ['nullable', Rule::in(self::GENDERS)],
            'religion' => $text,
            'mother_tongue' => $text,
            'caste' => $text,
            'huliya' => ['nullable', 'string', 'max:1000'],

            'mother_name' => $text,
            'mother_name_np' => $text,
            'father_name' => $text,
            'father_name_np' => $text,
            'grandfather_name' => $text,
            'grandfather_name_np' => $text,

            ...$this->addressRules('correspondence_'),
            'fax' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],

            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_UPLOAD_KB],
            'citizenship_front' => $this->documentRules(),
            'citizenship_back' => $this->documentRules(),
        ];
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateDatePair($validator, 'dob_ad', 'dob_bs')];
    }

    /** Validated fields ready to save, with the missing side of the birth date filled in. */
    public function profileData(): array
    {
        $data = collect($this->validated())
            ->except(['photo', 'citizenship_front', 'citizenship_back'])
            ->all();

        [$data['dob_ad'], $data['dob_bs']] = NepaliDate::reconcile($data['dob_ad'] ?? null, $data['dob_bs'] ?? null);

        return $data;
    }
}
