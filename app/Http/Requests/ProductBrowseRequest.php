<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductBrowseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable'],
            'condition' => ['nullable'],
            'min_price' => ['nullable'],
            'max_price' => ['nullable'],
            'sort' => ['nullable'],
        ];
    }

    /**
     * Get validated and sanitized filters for product query.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $rawQ = $this->query('q');
        $rawCondition = $this->query('condition');
        $rawSort = $this->query('sort');

        return [
            'q' => is_string($rawQ) && trim($rawQ) !== '' ? trim($rawQ) : null,
            'category' => filter_var($this->query('category'), FILTER_VALIDATE_INT) ?: null,
            'condition' => in_array($rawCondition, ['new', 'used'], true) ? $rawCondition : null,
            'min_price' => is_numeric($this->query('min_price')) && (int) $this->query('min_price') >= 0 ? (int) $this->query('min_price') : null,
            'max_price' => is_numeric($this->query('max_price')) && (int) $this->query('max_price') >= 0 ? (int) $this->query('max_price') : null,
            'sort' => in_array($rawSort, ['newest', 'price_asc', 'price_desc'], true) ? $rawSort : 'newest',
        ];
    }
}
