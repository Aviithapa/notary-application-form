<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Applicant\Concerns\ValidatesApplicantInput;
use App\Models\Exam;
use App\Models\Service;
use App\Support\NepaliDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ExamRequest extends FormRequest
{
    use ValidatesApplicantInput;

    /** AD/BS date pairs on the form, by column prefix. */
    private const DATES = ['application_start', 'application_end', 'double_fee_end', 'exam_date'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dates = [];
        foreach (self::DATES as $date) {
            $dates["{$date}_ad"] = ['nullable', 'date_format:Y-m-d'];
            $dates["{$date}_bs"] = ['nullable', 'string', 'max:10'];
        }

        return [
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'title_np' => ['nullable', 'string', 'max:255'],
            ...$dates,
            'start_time' => ['nullable', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'venue' => ['nullable', 'string', 'max:255'],
            'venue_np' => ['nullable', 'string', 'max:255'],
            'full_marks' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'pass_marks' => ['nullable', 'integer', 'min:0', 'lte:full_marks'],
            'status' => ['required', Rule::in(array_keys(Exam::STATUSES))],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            foreach (self::DATES as $date) {
                $this->validateDatePair($validator, "{$date}_ad", "{$date}_bs");
            }
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $ad = $this->datesAd();

            foreach (['application_start', 'application_end'] as $required) {
                if (! $ad[$required]) {
                    $validator->errors()->add("{$required}_bs", __('This date is required.'));
                }
            }

            // Each later date must not come before the one it follows
            $order = [
                'application_end' => ['application_start', __('The application end date must be on or after the start date.')],
                'double_fee_end' => ['application_end', __('The double fee date must be after the application end date.')],
                'exam_date' => [$ad['double_fee_end'] ? 'double_fee_end' : 'application_end', __('The exam date must be after the last application date.')],
            ];
            foreach ($order as $later => [$earlier, $message]) {
                $tooEarly = $later === 'application_end' ? $ad[$later] < $ad[$earlier] : $ad[$later] <= $ad[$earlier];
                if ($ad[$later] && $ad[$earlier] && $tooEarly) {
                    $validator->errors()->add("{$later}_bs", $message);
                }
            }
        }];
    }

    public function examData(): array
    {
        $data = $this->validated();

        // A service without an exam only has an application period: no schedule or marks
        if (! Service::find($data['service_id'])->requires_exam) {
            foreach (['exam_date_ad', 'exam_date_bs', 'start_time', 'duration_minutes', 'venue', 'venue_np', 'full_marks', 'pass_marks'] as $field) {
                $data[$field] = null;
            }
        }

        foreach (self::DATES as $date) {
            [$data["{$date}_ad"], $data["{$date}_bs"]] =
                NepaliDate::reconcile($data["{$date}_ad"] ?? null, $data["{$date}_bs"] ?? null);
        }

        return $data;
    }

    /** A.D. value of each date, converting from B.S. where only that was entered. */
    private function datesAd(): array
    {
        $ad = [];
        foreach (self::DATES as $date) {
            [$ad[$date]] = NepaliDate::reconcile($this->input("{$date}_ad"), $this->input("{$date}_bs"));
        }

        return $ad;
    }
}
