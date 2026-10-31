<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\ProductValidationRules;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProductUpdateRequest extends FormRequest
{
    use ProductValidationRules;

    /**
     * The resolved product instance.
     */
    protected ?Product $resolvedProduct = null;

    /**
     * Get the product being updated.
     */
    public function getTargetProduct(): Product
    {
        if ($this->resolvedProduct === null) {
            $param = $this->route('id') ?? $this->route('product');
            $this->resolvedProduct = $param instanceof Product
                ? $param
                : Product::findOrFail($param);
        }

        return $this->resolvedProduct;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('customer')?->can('update', $this->getTargetProduct()) ?? false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->cleanProductInput();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->commonRules(), [
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'delete_photos' => ['nullable', 'array'],
            'delete_photos.*' => ['integer'],
            'main_photo_id' => ['nullable', 'integer'],
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $product = $this->getTargetProduct();
            $existingPhotoIds = $product->images()->pluck('id')->all();
            $deletePhotoIds = array_map('intval', (array) $this->input('delete_photos', []));

            // Validate that deleted photos belong to this product
            foreach ($deletePhotoIds as $photoId) {
                if (! in_array($photoId, $existingPhotoIds, true)) {
                    $v->errors()->add('delete_photos', __('validation.exists', ['attribute' => __('validation.attributes.delete_photos')]));
                    break;
                }
            }

            // Validate chosen main photo if provided
            $mainPhotoId = $this->input('main_photo_id') !== null ? (int) $this->input('main_photo_id') : null;
            if ($mainPhotoId !== null) {
                if (! in_array($mainPhotoId, $existingPhotoIds, true)) {
                    $v->errors()->add('main_photo_id', __('validation.exists', ['attribute' => __('validation.attributes.main_photo_id')]));
                } elseif (in_array($mainPhotoId, $deletePhotoIds, true)) {
                    $v->errors()->add('main_photo_id', __('products.photo_count_invalid'));
                }
            }

            // Validate total photo count after deletions and additions (1 to 5)
            $remainingCount = count(array_diff($existingPhotoIds, $deletePhotoIds));
            $newPhotosCount = count((array) $this->file('photos', []));
            $finalCount = $remainingCount + $newPhotosCount;

            if ($finalCount < 1 || $finalCount > 5) {
                $v->errors()->add('photos', __('products.photo_count_invalid'));
            }
        });
    }
}
