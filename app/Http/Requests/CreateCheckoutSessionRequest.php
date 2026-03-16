<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCheckoutSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isCompanyAdmin();
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.required' => 'Selectați un plan.',
            'plan_id.exists'   => 'Planul selectat nu este valid.',
        ];
    }
}
