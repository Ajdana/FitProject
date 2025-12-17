<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'   => 'nullable|string|max:255',
            'age'         => 'nullable|integer|min:0|max:120',
            'avatar_path' => 'nullable|string|max:255',
            'gender'      => 'nullable|string|in:male,female,other',
            'height'      => 'nullable|integer|min:30|max:300',
            'weight'      => 'nullable|integer|min:1|max:500',
            'goal'        => 'nullable|string|max:255',
        ];
    }
}
