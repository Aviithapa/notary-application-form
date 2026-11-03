<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Unticked checkboxes are not submitted, so turn them into explicit false values. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_fee_based' => $this->boolean('is_fee_based'),
            'requires_exam' => $this->boolean('requires_exam'),
            'requires_application_form' => $this->boolean('requires_application_form'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_np' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'description_np' => ['nullable', 'string', 'max:2000'],
            'purpose' => ['required', Rule::in(array_keys(Service::PURPOSES))],
            'icon' => ['required', Rule::in(Service::ICONS)],
            'is_fee_based' => ['boolean'],
            'fee_amount' => ['nullable', 'required_if:is_fee_based,true', 'numeric', 'min:0', 'max:99999999'],
            'requires_exam' => ['boolean'],
            'requires_application_form' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function serviceData(): array
    {
        $data = $this->validated();
        $data['sort_order'] ??= 0;
        // A fee only applies to fee-based services
        if (! $data['is_fee_based']) {
            $data['fee_amount'] = null;
        }

        return $data;
    }
}
