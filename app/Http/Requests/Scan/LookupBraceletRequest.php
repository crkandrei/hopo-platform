<?php

namespace App\Http\Requests\Scan;

use Illuminate\Foundation\Http\FormRequest;

class LookupBraceletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $sanitized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim($this->input('code', ''))));
            $this->merge(['code' => $sanitized]);
        }
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'min:9',
                'max:50',
                'regex:/^[A-Z0-9]+$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.min' => 'Cod prea scurt — scanați din nou.',
            'code.regex' => 'Format cod invalid — scanați din nou.',
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


