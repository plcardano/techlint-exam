<?php

namespace App\Http\Requests\v1\IpAddress;

use App\Models\IpAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIpAddressRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ip_address' => ['required', 'string', 'max:45', 'ip', Rule::unique(IpAddress::class)],
            'label' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
