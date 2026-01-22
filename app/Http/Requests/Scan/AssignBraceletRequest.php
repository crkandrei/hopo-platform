<?php

namespace App\Http\Requests\Scan;

use Illuminate\Foundation\Http\FormRequest;

class AssignBraceletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check();
    }

    public function rules(): array
    {
        return [
            'bracelet_code' => [
                'required',
                'string',
                'min:1',
                'max:50',
            ],
            'child_id' => ['required', 'exists:children,id'],
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


