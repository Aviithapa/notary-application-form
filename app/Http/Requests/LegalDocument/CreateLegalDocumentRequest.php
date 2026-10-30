<?php

namespace App\Http\Requests\LegalDocument;

use Illuminate\Foundation\Http\FormRequest;

class CreateLegalDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'nullable|max:255',
            'title_np' => 'nullable|max:255',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|mimes:pdf|max:20480',
        ];
    }

    public function messages(): array
    {
        return [
            'files.*.mimes' => 'Only PDF files are allowed.',
            'files.*.max' => 'Each file must be smaller than 20 MB.',
        ];
    }
}
