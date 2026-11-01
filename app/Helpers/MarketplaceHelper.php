<?php

namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class MarketplaceHelper
{
    /**
     * Format a price with thousand separators and IQD currency symbol.
     */
    public static function formatPrice(int $price): string
    {
        return number_format($price).' '.__('products.iqd_currency');
    }

    /**
     * Convert an Iraqi local phone number to a valid WhatsApp international link.
     */
    public static function whatsappUrl(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9+]/', '', trim($phone)) ?? '';

        if (str_starts_with($cleaned, '+964')) {
            $international = substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '07')) {
            $international = '964'.substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '7')) {
            $international = '964'.$cleaned;
        } else {
            $international = ltrim($cleaned, '+');
        }

        return 'https://wa.me/'.$international;
    }

    /**
     * Get the main image URL for a product.
     */
    public static function mainImageUrl(?Product $product): ?string
    {
        if (! $product || $product->images->isEmpty()) {
            return null;
        }

        $mainImage = $product->images->firstWhere('is_main', true) ?? $product->images->first();

        return $mainImage ? asset('storage/'.$mainImage->image_path) : null;
    }

    /**
     * Get the thumbnail image URL (400px) for a product card/list.
     * Falls back to full image if thumbnail does not exist.
     */
    public static function thumbnailImageUrl(?Product $product): ?string
    {
        if (! $product || $product->images->isEmpty()) {
            return null;
        }

        $mainImage = $product->images->firstWhere('is_main', true) ?? $product->images->first();
        if (! $mainImage) {
            return null;
        }

        $thumbRelPath = dirname($mainImage->image_path).'/thumb_'.basename($mainImage->image_path);
        if (Storage::disk('public')->exists($thumbRelPath)) {
            return asset('storage/'.$thumbRelPath);
        }

        return asset('storage/'.$mainImage->image_path);
    }
}
