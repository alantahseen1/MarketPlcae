<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    /**
     * Create a new product with images.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $photos
     */
    public function create(Customer $customer, array $data, array $photos): Product
    {
        $product = Product::create([
            'customer_id' => $customer->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'condition' => $data['condition'],
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
        ]);

        foreach ($photos as $index => $photo) {
            $path = $photo->store("products/{$product->id}", 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'is_main' => $index === 0,
                'sort_order' => $index,
            ]);
        }

        return $product;
    }

    /**
     * Update an existing product, delete requested photos, upload new ones,
     * and handle main photo reassignment.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $newPhotos
     * @param  array<int, int>  $deletePhotoIds
     */
    public function update(
        Product $product,
        array $data,
        array $newPhotos = [],
        array $deletePhotoIds = [],
        ?int $mainPhotoId = null
    ): Product {
        // If an approved or rejected product is edited, reset it to pending
        if (in_array($product->status, ['approved', 'rejected'], true)) {
            $data['status'] = 'pending';
            $data['reviewed_by'] = null;
            $data['reviewed_at'] = null;
            $data['rejection_reason'] = null;
        }

        $product->update([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'condition' => $data['condition'],
            'status' => $data['status'] ?? $product->status,
            'reviewed_by' => array_key_exists('reviewed_by', $data) ? $data['reviewed_by'] : $product->reviewed_by,
            'reviewed_at' => array_key_exists('reviewed_at', $data) ? $data['reviewed_at'] : $product->reviewed_at,
            'rejection_reason' => array_key_exists('rejection_reason', $data) ? $data['rejection_reason'] : $product->rejection_reason,
        ]);

        // 1. Delete requested photos from database and disk
        if (! empty($deletePhotoIds)) {
            $imagesToDelete = $product->images()->whereIn('id', $deletePhotoIds)->get();

            foreach ($imagesToDelete as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        // 2. Upload new photos
        $currentMaxSort = (int) ($product->images()->max('sort_order') ?? -1);

        foreach ($newPhotos as $photo) {
            $path = $photo->store("products/{$product->id}", 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'is_main' => false,
                'sort_order' => ++$currentMaxSort,
            ]);
        }

        // 3. Handle main photo selection and fallback
        $remainingImages = $product->images()->orderBy('sort_order')->orderBy('id')->get();

        if ($mainPhotoId !== null && $remainingImages->contains('id', $mainPhotoId)) {
            ProductImage::where('product_id', $product->id)->update(['is_main' => false]);
            ProductImage::where('id', $mainPhotoId)->update(['is_main' => true]);
        } else {
            $hasMain = $remainingImages->contains('is_main', true);

            if (! $hasMain && $remainingImages->isNotEmpty()) {
                $firstImage = $remainingImages->first();
                $firstImage->update(['is_main' => true]);
            }
        }

        return $product->fresh(['images', 'category']);
    }

    /**
     * Soft-remove a product by setting status to removed.
     * The row and files are never erased.
     */
    public function remove(Product $product): void
    {
        $product->update([
            'status' => 'removed',
        ]);
    }

    /**
     * Mark an approved product as sold.
     */
    public function markAsSold(Product $product): void
    {
        $product->update([
            'status' => 'sold',
            'sold_at' => now(),
        ]);
    }
}
