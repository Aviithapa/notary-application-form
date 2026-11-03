<?php

namespace App\Http\Requests\Applicant;

use App\Http\Requests\Applicant\Concerns\ValidatesApplicantInput;
use App\Models\Exam;
use App\Models\ExamApplication;
use Illuminate\Foundation\Http\FormRequest;

class ExamApplicationRequest extends FormRequest
{
    use ValidatesApplicantInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Exam $exam */
        $exam = $this->route('exam');
        $fee = $exam->currentFee();
        $image = ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_UPLOAD_KB];

        $rules = [
            ExamApplication::SIGNATURE => $image,
            ExamApplication::THUMB_LEFT => $image,
            ExamApplication::THUMB_RIGHT => $image,
            'declaration' => ['accepted'],
        ];

        // Payment proof only for fee-based services; the amount must cover today's fee
        if ($fee !== null) {
            $rules[ExamApplication::VOUCHER] = ['required', 'file', self::DOCUMENT_MIMES, 'max:' . self::MAX_UPLOAD_KB];
            $rules['paid_amount'] = ['required', 'numeric', 'min:' . $fee, 'max:99999999'];
        }

        return $rules;
    }

    public function messages(): array
    {
        /** @var Exam $exam */
        $exam = $this->route('exam');

        return [
            'paid_amount.min' => __('The amount paid must be at least Rs. :fee.', ['fee' => number_format((float) $exam->currentFee(), 2)]),
            'declaration.accepted' => __('Please confirm the declaration.'),
        ];
    }
}
