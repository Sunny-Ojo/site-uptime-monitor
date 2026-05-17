<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMonitorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'url', 'unique:monitors,url'],
            'check_interval' => ['nullable', 'integer', 'min:1', 'max:60'],
            'threshold' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
