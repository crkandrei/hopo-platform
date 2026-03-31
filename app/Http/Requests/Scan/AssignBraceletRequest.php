<?php

namespace App\Http\Requests\Scan;

use Illuminate\Foundation\Http\FormRequest;

class AssignBraceletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('bracelet_code') && $this->input('bracelet_code') !== null) {
            $sanitized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim($this->input('bracelet_code', ''))));
            $this->merge(['bracelet_code' => $sanitized ?: null]);
        }
    }

    public function rules(): array
    {
        return [
            'bracelet_code' => [
                'nullable',
                'string',
                'min:9',
                'max:50',
                'regex:/^[A-Z0-9]+$/',
            ],
            'child_id' => ['required', 'exists:children,id'],
            'session_type' => ['nullable', 'string', 'in:normal,birthday'],
            'pre_checkin_token' => ['nullable', 'string', 'uuid'],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson() || $this->is('scan-api/*')) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
        
        parent::failedValidation($validator);
    }
}


