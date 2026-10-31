<?php

namespace App\Http\Requests\Traits;

use Illuminate\Validation\Rule;

trait ProductValidationRules
{
    /**
     * Clean up input before validation.
     */
    protected function cleanProductInput(): void
    {
        if ($this->has('price')) {
            $cleanedPrice = str_replace([',', ' ', '،'], '', (string) $this->input('price'));
            $this->merge(['price' => $cleanedPrice]);
        }
    }

    /**
     * Common validation rules for products.
     *
     * @return array<string, mixed>
     */
    protected function commonRules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('category', 'id')->where('is_active', true),
            ],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:1'],
            'condition' => ['required', 'in:new,used'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ];
    }
}

