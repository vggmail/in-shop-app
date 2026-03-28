<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->withCount('children', 'items')->get();
        $allCategories = Category::all();
        return view("admin.categories.index", compact("categories", "allCategories"));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'image_file' => 'nullable|image|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = $request->except('image_file');
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('categories', 'public');
        }

        Category::create($data);
        return redirect()->back()->with("success", "Category created successfully.");
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id|not_in:'.$id,
            'image_file' => 'nullable|image|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = $request->except('image_file');
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        if ($request->hasFile('image_file')) {
            // Delete old image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image_file')->store('categories', 'public');
        }

        $category->update($data);
        return redirect()->back()->with("success", "Category updated successfully.");
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Prevent deletion if category has items
        if ($category->items()->exists()) {
            return redirect()->back()->with("error", "Cannot delete category: It has items assigned to it.");
        }

        // Prevent deletion if category has subcategories
        if ($category->children()->exists()) {
            return redirect()->back()->with("error", "Cannot delete category: It has subcategories assigned to it.");
        }
        
        // For soft deletes, we keep the image so it can be restored
        // if ($category->image) {
        //     Storage::disk('public')->delete($category->image);
        // }
        
        $category->delete();
        return redirect()->back()->with("success", "Category deleted successfully.");
    }
}
