<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\TransactionDetail;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $products = Product::with(['kategori', 'supplier'])
            ->when($search, function($query) use ($search) {
                $query->where('nama_barang', 'like', '%' . $search . '%')
                      ->orWhereHas('kategori', function($q) use ($search) {
                          $q->where('nama_kategori', 'like', '%' . $search . '%');
                      });
            })
            ->get();

        return view('landing.product.index', compact('products', 'search'));
    }

    public function show($id)
    {
        $product = Product::with(['kategori', 'supplier'])
                    ->where('barang_id', $id)
                    ->firstOrFail();

        $products = Product::where('kategori_id', $product->kategori_id)
                    ->where('barang_id', '!=', $product->barang_id)
                    ->limit(5)
                    ->inRandomOrder()
                    ->get();

        $transaction = TransactionDetail::with('product')
                        ->where('barang_id', $product->barang_id)
                        ->get();

        return view('landing.product.show', compact('product', 'products', 'transaction'));
    }
}