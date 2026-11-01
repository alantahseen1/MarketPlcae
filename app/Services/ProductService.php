<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            $path = $this->storeProcessedPhoto($photo, $product->id);

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

                $thumbPath = dirname($image->image_path).'/thumb_'.basename($image->image_path);
                if (Storage::disk('public')->exists($thumbPath)) {
                    Storage::disk('public')->delete($thumbPath);
                }

                $image->delete();
            }
        }

        // 2. Upload new photos
        $currentMaxSort = (int) ($product->images()->max('sort_order') ?? -1);

        foreach ($newPhotos as $photo) {
            $path = $this->storeProcessedPhoto($photo, $product->id);

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
     * Resize uploaded image so longest side is max 1600px at JPEG quality 82,
     * and generate a 400px thumbnail alongside.
     *
     * @return string Relative path of full image on public disk
     */
    protected function storeProcessedPhoto(UploadedFile $photo, int $productId): string
    {
        $dir = "products/{$productId}";
        Storage::disk('public')->makeDirectory($dir);

        $filename = Str::random(40).'.jpg';
        $fullRelPath = "{$dir}/{$filename}";
        $thumbRelPath = "{$dir}/thumb_{$filename}";

        $fullDiskPath = Storage::disk('public')->path($fullRelPath);
        $thumbDiskPath = Storage::disk('public')->path($thumbRelPath);

        $srcPath = $photo->getRealPath();
        $src = null;

        $mime = $photo->getMimeType();
        if ($mime === 'image/jpeg') {
            $src = @imagecreatefromjpeg($srcPath);
        } elseif ($mime === 'image/png') {
            $src = @imagecreatefrompng($srcPath);
        } elseif ($mime === 'image/webp') {
            $src = @imagecreatefromwebp($srcPath);
        }

        if (! $src) {
            $content = file_get_contents($srcPath);
            if ($content !== false) {
                $src = @imagecreatefromstring($content);
            }
        }

        // If GD cannot process, store raw file
        if (! $src) {
            return $photo->store($dir, 'public');
        }

        $origW = imagesx($src);
        $origH = imagesy($src);

        // 1. Full Image (longest side max 1600px)
        if ($origW > 1600 || $origH > 1600) {
            if ($origW >= $origH) {
                $newW = 1600;
                $newH = (int) max(1, round(($origH * 1600) / $origW));
            } else {
                $newH = 1600;
                $newW = (int) max(1, round(($origW * 1600) / $origH));
            }
        } else {
            $newW = $origW;
            $newH = $origH;
        }

        $fullImg = imagecreatetruecolor($newW, $newH);
        $white = imagecolorallocate($fullImg, 255, 255, 255);
        imagefilledrectangle($fullImg, 0, 0, $newW, $newH, $white);
        imagecopyresampled($fullImg, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagejpeg($fullImg, $fullDiskPath, 82);
        imagedestroy($fullImg);

        // 2. Thumbnail (longest side max 400px)
        if ($origW >= $origH) {
            $thumbW = min(400, $origW);
            $thumbH = (int) max(1, round(($origH * $thumbW) / $origW));
        } else {
            $thumbH = min(400, $origH);
            $thumbW = (int) max(1, round(($origW * $thumbH) / $origH));
        }

        $thumbImg = imagecreatetruecolor($thumbW, $thumbH);
        $thumbWhite = imagecolorallocate($thumbImg, 255, 255, 255);
        imagefilledrectangle($thumbImg, 0, 0, $thumbW, $thumbH, $thumbWhite);
        imagecopyresampled($thumbImg, $src, 0, 0, 0, 0, $thumbW, $thumbH, $origW, $origH);
        imagejpeg($thumbImg, $thumbDiskPath, 82);
        imagedestroy($thumbImg);

        imagedestroy($src);

        return $fullRelPath;
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
