<?php

namespace App\Http\Requests\v1\IpAddress;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIpAddressRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
