<?php

namespace App\Http\Requests\Applicant;

use App\Http\Requests\Applicant\Concerns\ValidatesApplicantInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TrainingsRequest extends FormRequest
{
    use ValidatesApplicantInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $text = ['nullable', 'string', 'max:255'];

        return [
            'trainings' => ['nullable', 'array', 'max:20'],
            'trainings.*.id' => ['nullable', 'integer'],
            'trainings.*.training_name' => $text,
            'trainings.*.certificate_number' => $text,
            'trainings.*.organization' => $text,
            'trainings.*.issue_date_ad' => ['nullable', 'date_format:Y-m-d'],
            'trainings.*.issue_date_bs' => ['nullable', 'string', 'max:10'],
            'trainings.*.certificate' => $this->documentRules(),
            'trainings.*.remove_certificate' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            foreach (array_keys((array) $this->input('trainings', [])) as $i) {
                $this->validateDatePair($validator, "trainings.$i.issue_date_ad", "trainings.$i.issue_date_bs");
            }
        }];
    }
}
