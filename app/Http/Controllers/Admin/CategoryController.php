<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display all categories
     */
    public function index()
    {
        $categories = Category::withCount(['products', 'subcategories'])
            ->with('subcategories')
            ->orderBy('sort_order')
            ->get();

        $stats = [
            'total_categories' => Category::count(),
            'total_subcategories' => Subcategory::count(),
            'featured' => Category::where('is_featured', true)->count(),
            'total_products' => Category::withCount('products')->get()->sum('products_count'),
        ];

        return view('admin.categories.index', compact('categories', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.categories.form');
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_featured'] = $request->has('is_featured');
        $validated['sort_order'] = $validated['sort_order'] ?? Category::max('sort_order') + 1;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('categories', $filename, 'public');
            $validated['image'] = $path;
        }

        $category = Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Show edit form
     */
    public function edit(Category $category)
    {
        $category->load('subcategories');
        return view('admin.categories.form', compact('category'));
    }

    /**
     * Update category
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_featured'] = $request->has('is_featured');

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('categories', $filename, 'public');
            $validated['image'] = $path;
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Delete category
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete category with existing products. Please move or delete products first.');
        }

        // Delete subcategories
        foreach ($category->subcategories as $subcategory) {
            if ($subcategory->products()->exists()) {
                return back()->with('error', 'Cannot delete category. Subcategories have products.');
            }
            $subcategory->delete();
        }

        // Delete image
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }

    /**
     * Reorder categories
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:categories,id',
            'order.*.position' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            Category::where('id', $item['id'])->update(['sort_order' => $item['position']]);
        }

        return response()->json(['success' => true, 'message' => 'Order updated successfully']);
    }

    /**
     * Subcategory Management
     */
    public function createSubcategory(Category $category)
    {
        return view('admin.categories.subcategory-form', compact('category'));
    }

    public function storeSubcategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['category_id'] = $category->id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['sort_order'] = $validated['sort_order'] ?? $category->subcategories()->max('sort_order') + 1;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('subcategories', $filename, 'public');
            $validated['image'] = $path;
        }

        $category->subcategories()->create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Subcategory created successfully!');
    }

    public function editSubcategory(Subcategory $subcategory)
    {
        $category = $subcategory->category;
        return view('admin.categories.subcategory-form', compact('category', 'subcategory'));
    }

    public function updateSubcategory(Request $request, Subcategory $subcategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($subcategory->image) {
                Storage::disk('public')->delete($subcategory->image);
            }

            $image = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('subcategories', $filename, 'public');
            $validated['image'] = $path;
        }

        $subcategory->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Subcategory updated successfully!');
    }

    public function destroySubcategory(Subcategory $subcategory)
    {
        if ($subcategory->products()->exists()) {
            return back()->with('error', 'Cannot delete subcategory with existing products.');
        }

        if ($subcategory->image) {
            Storage::disk('public')->delete($subcategory->image);
        }

        $subcategory->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Subcategory deleted successfully!');
    }
}