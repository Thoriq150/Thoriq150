<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $categories = Category::when($search, function($query) use($search){
            $query->where('nama_kategori', 'like', '%'.$search.'%');
        })->get();

        return view('landing.category.index', compact('categories', 'search'));
    }

    public function show($id)
    {
        $category = Category::where('kategori_id', $id)->first();

        if (!$category) {
            abort(404);
        }

        $products = Product::where('kategori_id', $category->kategori_id)->get();

        return view('landing.category.show', compact('category', 'products'));
    }
}
