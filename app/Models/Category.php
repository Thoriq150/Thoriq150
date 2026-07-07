<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $table = 'kategori';
    protected $primaryKey = 'kategori_id';

    protected $fillable = [
        'nama_kategori',
        'image' // ✅ WAJIB ditambahkan
    ];

    // ✅ FIX route model binding (biar tidak cari "id")
    public function getRouteKeyName()
    {
        return 'kategori_id';
    }

    // relasi ke barang
    public function products()
    {
        return $this->hasMany(Product::class, 'kategori_id');
    }
}