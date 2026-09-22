<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    // Show all categories
    public function index()
    {
        $categories = Category::where('user_id', auth()->id())->get();

        return view('categories.index', compact('categories'));
    }

    // Show create page
    public function create()
    {
        return view('categories.create');
    }

    // Store category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255'
        ]);

        Category::create([
            'user_id' => auth()->id(),
            'name' => $request->name
        ]);

        return redirect('/categories');
    }

    // Edit page
    public function edit($id)
    {
        $category = Category::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        return view('categories.edit', compact('category'));
    }

    // Update category
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255'
        ]);

        $category = Category::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $category->update([
            'name' => $request->name
        ]);

        return redirect('/categories');
    }

    // Delete category
    public function destroy($id)
    {
        $category = Category::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $category->delete();

        return redirect('/categories');
    }
}