<?php

namespace App\Http\Requests\Scan;

use Illuminate\Foundation\Http\FormRequest;

class CreateChildRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check();
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'], // Keep first_name for backward compatibility with frontend
            'allergies' => ['nullable', 'string', 'max:500'],
            // Either guardian_id OR guardian_* fields (enforced in withValidator)
            'guardian_id' => ['nullable', 'integer', 'exists:guardians,id'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'bracelet_code' => [
                'nullable',
                'string',
                'min:9',
                'max:50',
                'regex:/^[A-Z0-9]+$/',
            ],
            'session_type' => ['nullable', 'string', 'in:normal,birthday'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $fields = [
            'guardian_name' => $this->normalizeEmpty($this->input('guardian_name')),
            'guardian_phone' => $this->normalizeEmpty($this->input('guardian_phone')),
        ];

        if ($this->has('bracelet_code') && $this->input('bracelet_code') !== null) {
            $sanitized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim($this->input('bracelet_code', ''))));
            $fields['bracelet_code'] = $sanitized ?: null;
        }

        $this->merge($fields);
    }

    private function normalizeEmpty($value)
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed === '' ? null : $trimmed;
        }
        return $value;
    }

    public function messages(): array
    {
        return [];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();
            $hasId = !empty($data['guardian_id']);
            $hasNew = !empty($data['guardian_name']);

            if (!$hasId && !$hasNew) {
                $validator->errors()->add('guardian', 'Alege părinte existent sau introdu date părinte.');
            }
            if ($hasId && $hasNew) {
                $validator->errors()->add('guardian', 'Nu poți trimite și guardian_id și date părinte.');
            }

            // When creating a new guardian, require minimal fields
            if ($hasNew) {
                if (empty($data['guardian_phone'])) {
                    $validator->errors()->add('guardian_phone', 'Telefonul părintelui este necesar.');
                }
            }
        });
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson() || $this->is('scan-api/*')) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
        
        parent::failedValidation($validator);
    }
}


