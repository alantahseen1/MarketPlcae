<?php

namespace App\Http\Requests\Traits;

use Illuminate\Validation\Rule;

trait CategoryValidationRules
{
    /**
     * Get common validation rules for categories.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function categoryRules(?int $ignoreId = null): array
    {
        $uniqueRule = Rule::unique('category', 'name');
        if ($ignoreId !== null) {
            $uniqueRule->ignore($ignoreId);
        }

        return [
            'name' => ['required', 'string', 'max:100', $uniqueRule],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
