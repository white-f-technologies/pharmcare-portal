<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::paginate(10);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        if (!$request->filled('slug')) {
            $request->merge(['slug' => Str::slug($request->input('name'))]);
        } else {
            $request->merge(['slug' => Str::slug($request->input('slug'))]);
        }

        $validated = $request->validate([
            'name' => 'required|max:255',
            'slug' => 'required|unique:categories,slug',
            'description' => 'nullable',
        ]);

        $category = Category::create($validated);

        $this->logActivity('category_created', 'Category', $category->id, "Category '{$category->name}' created.");

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        if (!$request->filled('slug')) {
            $request->merge(['slug' => Str::slug($request->input('name'))]);
        } else {
            $request->merge(['slug' => Str::slug($request->input('slug'))]);
        }

        $validated = $request->validate([
            'name' => 'required|max:255',
            'slug' => 'required|unique:categories,slug,' . $category->id,
            'description' => 'nullable',
        ]);

        $category->update($validated);

        $this->logActivity('category_updated', 'Category', $category->id, "Category '{$category->name}' updated.");

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->medicines()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category because it contains medicines.');
        }

        $this->logActivity('category_deleted', 'Category', $category->id, "Category '{$category->name}' deleted.");
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
