<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => trim((string) $this->phone),
            ]);
        }

        if ($this->input('type') === 'regular') {
            if ($this->input('shop_name') === '') {
                $this->merge(['shop_name' => null]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:regular,shop'],
            'full_name' => ['required', 'string', 'max:100'],
            'shop_name' => [
                'required_if:type,shop',
                'nullable',
                'prohibited_if:type,regular',
                'string',
                'max:100',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
                'prohibited_if:type,regular',
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^(?:\+9647|07)[0-9]{9}$/',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                'unique:customer,email',
            ],
            'address' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
