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
        // Only the owner may resubmit an application
        return ! $this->resubmitting() || $this->route('application')->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        $fee = $this->fee();
        // A resubmission keeps its current files unless new ones are chosen
        $presence = $this->resubmitting() ? 'nullable' : 'required';
        $image = [$presence, 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_UPLOAD_KB];

        $rules = [
            ExamApplication::SIGNATURE => $image,
            ExamApplication::THUMB_LEFT => $image,
            ExamApplication::THUMB_RIGHT => $image,
            'declaration' => ['accepted'],
        ];

        // Payment proof only for fee-based services; the amount must cover the fee
        if ($fee !== null) {
            $rules[ExamApplication::VOUCHER] = [$presence, 'file', self::DOCUMENT_MIMES, 'max:' . self::MAX_UPLOAD_KB];
            $rules['paid_amount'] = ['required', 'numeric', 'min:' . $fee, 'max:99999999'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'paid_amount.min' => __('The amount paid must be at least Rs. :fee.', ['fee' => number_format((float) $this->fee(), 2)]),
            'declaration.accepted' => __('Please confirm the declaration.'),
        ];
    }

    /** True on the resubmit route, which carries the existing application instead of the exam. */
    public function resubmitting(): bool
    {
        return $this->route('application') instanceof ExamApplication;
    }

    /** Fee due: fixed on the application when resubmitting, otherwise today's fee for the exam. */
    public function fee(): ?string
    {
        if ($this->resubmitting()) {
            return $this->route('application')->fee_amount;
        }

        /** @var Exam $exam */
        $exam = $this->route('exam');

        return $exam->currentFee();
    }
}
