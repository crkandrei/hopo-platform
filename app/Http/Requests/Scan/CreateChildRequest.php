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
                'min:1',
                'max:50',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalize empty strings to null for guardian optional fields
        $this->merge([
            'guardian_name' => $this->normalizeEmpty($this->input('guardian_name')),
            'guardian_phone' => $this->normalizeEmpty($this->input('guardian_phone')),
        ]);
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


