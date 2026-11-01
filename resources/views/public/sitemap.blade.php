{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- Home Page --}}
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- Products Index --}}
    <url>
        <loc>{{ route('products.index') }}</loc>
        <changefreq>always</changefreq>
        <priority>0.9</priority>
    </url>

    {{-- Categories --}}
    @foreach($categories as $category)
        <url>
            <loc>{{ route('products.index', ['category' => $category->id]) }}</loc>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    {{-- Active Products --}}
    @foreach($products as $product)
        <url>
            <loc>{{ route('products.show', $product->id) }}</loc>
            @if($product->updated_at)
                <lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>
            @endif
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    {{-- Active Shops --}}
    @foreach($shops as $shop)
        <url>
            <loc>{{ route('shops.show', $shop->id) }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach
</urlset>

