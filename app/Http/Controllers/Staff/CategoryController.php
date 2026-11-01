<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with product counts.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('id')
            ->get();

        return view('staff.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        $category = new Category([
            'is_active' => true,
        ]);

        return view('staff.categories.form', compact('category'));
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        Category::create([
            'name' => trim((string) $request->input('name')),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('staff.categories.index')
            ->with('success', __('staff.category_created'));
    }

    /**
     * Show the form for editing the category.
     */
    public function edit(int $id): View
    {
        $category = Category::query()->findOrFail($id);

        return view('staff.categories.form', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(CategoryUpdateRequest $request, int $id): RedirectResponse
    {
        $category = Category::query()->findOrFail($id);

        $category->update([
            'name' => trim((string) $request->input('name')),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('staff.categories.index')
            ->with('success', __('staff.category_updated'));
    }

    /**
     * Toggle the active status of a category.
     */
    public function toggle(int $id): RedirectResponse
    {
        $category = Category::query()->findOrFail($id);

        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        $msg = $category->is_active
            ? __('staff.category_activated_success')
            : __('staff.category_deactivated_success');

        return redirect()
            ->back()
            ->with('success', $msg);
    }
}

