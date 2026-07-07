<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function __invoke(Request $request)
    {
        $search = $request->search;

        $products = Product::with('kategori', 'supplier')
            ->when($search, function($query) use($search){
                $query->where('nama_barang', 'like', '%'.$search.'%');
            })
            ->orderBy('barang_id', 'desc')
            ->paginate(6);

        $categories = Category::with('products')->limit(12)->get();

        return view('landing.welcome', compact('products', 'categories', 'search'));
    }
}