<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => trim((string) $this->phone),
            ]);
        }

        $customer = $this->user('customer');
        if ($customer && $customer->type === 'regular') {
            if ($this->input('shop_name') === '') {
                $this->merge(['shop_name' => null]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $customer = $this->user('customer');
        $isShop = $customer && $customer->type === 'shop';

        return [
            'full_name' => ['required', 'string', 'max:100'],
            'shop_name' => $isShop
                ? ['required', 'string', 'max:100']
                : ['prohibited', 'nullable'],
            'phone' => [
                'required',
                'string',
                'regex:/^(?:\+9647|07)[0-9]{9}$/',
            ],
            'address' => ['required', 'string', 'max:255'],
            'logo' => $isShop
                ? ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048']
                : ['prohibited'],
        ];
    }
}

