<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\CategoryValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class CategoryUpdateRequest extends FormRequest
{
    use CategoryValidationRules;

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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $id = (int) $this->route('id');

        return $this->categoryRules($id);
    }
}
