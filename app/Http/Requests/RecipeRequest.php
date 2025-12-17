<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'image'        => 'nullable|string|max:255',
            'products'     => 'required|array|min:1',
            'products.*'   => 'string|max:255',
            'instructions'=> 'required|string',
            'calories'     => 'nullable|integer|min:0',
        ];
    }
}
