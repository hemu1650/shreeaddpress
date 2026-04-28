<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // 🔹 List Page
    public function index()
    {
        $categories = Category::latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    // 🔹 Create
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories,name'
        ]);

        Category::create([
            'name' => $request->name
        ]);

        return back()->with('success', 'Category added');
    }

    // 🔹 Update
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:categories,name,'.$id
        ]);

        $category->update([
            'name' => $request->name
        ]);

        return back()->with('success', 'Category updated');
    }

    // 🔹 Delete
    public function destroy($id)
    {
        Category::findOrFail($id)->delete();

        return back()->with('success', 'Category deleted');
    }
}