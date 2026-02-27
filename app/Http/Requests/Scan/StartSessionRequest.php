<?php

namespace App\Http\Requests\Scan;

use Illuminate\Foundation\Http\FormRequest;

class StartSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check();
    }

    public function rules(): array
    {
        return [
            'child_id' => ['required', 'exists:children,id'],
            'bracelet_code' => [
                'nullable',
                'string',
                'min:1',
                'max:50',
            ],
        ];
    }
}


