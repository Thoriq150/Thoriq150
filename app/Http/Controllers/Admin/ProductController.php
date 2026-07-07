<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Traits\HasImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use HasImage;

    public function index()
    {
        $products = Product::orderBy('barang_id', 'desc')->paginate(10);

        return view('admin.product.index', compact('products'));
    }

    public function create()
    {
        $suppliers = Supplier::all();

        $categories = Category::all();

        return view('admin.product.create', compact('suppliers', 'categories'));
    }

    public function store(ProductRequest $request)
    {
        Product::create([

            'nama_barang' => $request->nama_barang,

            'stok' => $request->stok,

            'satuan' => $request->satuan,

            'harga' => $request->harga,

            'image' => 'default.jpg',

            'kategori_id' => $request->kategori_id,

            'supplier_id' => $request->supplier_id,
        ]);

        return redirect(route('admin.product.index'))
            ->with('toast_success', 'Barang berhasil ditambahkan');
    }

    public function edit(Product $product)
    {
        $suppliers = Supplier::all();

        $categories = Category::all();

        return view('admin.product.edit', compact(
            'product',
            'suppliers',
            'categories'
        ));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update([

            'nama_barang' => $request->nama_barang,

            'stok' => $request->stok,

            'satuan' => $request->satuan,

            'harga' => $request->harga,

            'kategori_id' => $request->kategori_id,

            'supplier_id' => $request->supplier_id,
        ]);

        return redirect(route('admin.product.index'))
            ->with('toast_success', 'Barang berhasil diubah');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with(
            'toast_success',
            'Barang berhasil dihapus'
        );
    }
}