@props(['status'])

@if($status === 'pending')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
        {{ __('products.status_pending') }}
    </span>
@elseif($status === 'approved')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
        {{ __('products.status_approved') }}
    </span>
@elseif($status === 'rejected')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
        {{ __('products.status_rejected') }}
    </span>
@elseif($status === 'sold')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-300">
        {{ __('products.status_sold') }}
    </span>
@elseif($status === 'removed')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-600 border border-gray-400">
        {{ __('products.status_removed') }}
    </span>
@endif

