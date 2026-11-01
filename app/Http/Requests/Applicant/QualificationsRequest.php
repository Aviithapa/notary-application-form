<?php

namespace App\Http\Requests\Applicant;

use App\Http\Requests\Applicant\Concerns\ValidatesApplicantInput;
use Illuminate\Foundation\Http\FormRequest;

class QualificationsRequest extends FormRequest
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
            'qualifications' => ['nullable', 'array', 'max:20'],
            'qualifications.*.id' => ['nullable', 'integer'],
            'qualifications.*.university_name' => $text,
            'qualifications.*.passed_year' => ['nullable', 'string', 'max:10'],
            'qualifications.*.level' => $text,
            'qualifications.*.division' => $text,
            'qualifications.*.percentage' => ['nullable', 'string', 'max:20'],
            'qualifications.*.documents' => ['nullable', 'array', 'max:10'],
            'qualifications.*.documents.*' => $this->documentRules(),
            'qualifications.*.remove_documents' => ['nullable', 'array'],
            'qualifications.*.remove_documents.*' => ['integer'],
        ];
    }
}
