<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Traits\HasImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    use HasImage;

    public function index(Request $request)
    {
        $search = $request->search;

        $categories = Category::when($search, function($query) use($search){
            $query->where('nama_kategori', 'like', '%'.$search.'%');
        })->paginate(10);

        return view('admin.category.index', compact('categories', 'search'));
    }

    public function store(CategoryRequest $request)
    {
        $image = $this->uploadImage($request, 'public/categories/', 'image');

        Category::create([
            'nama_kategori' => $request->name, // pastikan form pakai name
            'image' => $image ? $image->hashName() : null,
        ]);

        return back()->with('toast_success', 'Kategori Berhasil Ditambahkan');
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update([
            'nama_kategori' => $request->name,
        ]);

        // ✅ hanya upload kalau ada file
        if ($request->hasFile('image')) {
            $image = $this->uploadImage($request, 'public/categories/', 'image');

            $this->updateImage(
                'public/categories/',
                'image',
                $category,
                $image->hashName()
            );
        }

        return back()->with('toast_success', 'Kategori Berhasil Diubah');
    }

    public function destroy(Category $category)
    {
        if ($category->image) {
            Storage::disk('local')->delete('public/categories/' . basename($category->image));
        }

        $category->delete();

        return back()->with('toast_success', 'Kategori Berhasil Dihapus');
    }
}