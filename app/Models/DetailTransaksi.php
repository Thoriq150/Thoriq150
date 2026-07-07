<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi';

    protected $primaryKey = 'detail_id';

    public $timestamps = false;

   protected $fillable = [
    'transaksi_keluar_id',
    'transaksi_masuk_id',
    'barang_id',
    'jumlah'
];

    public function transaksiKeluar()
    {
        return $this->belongsTo(
            TransaksiKeluar::class,
            'transaksi_keluar_id',
            'transaksi_keluar_id'
        );
    }

    public function barang()
    {
        return $this->belongsTo(
            Product::class,
            'barang_id',
            'barang_id'
        );
    }


    public function transaksiMasuk()
    {
        return $this->belongsTo(
            TransaksiMasuk::class,
            'transaksi_masuk_id',
            'transaksi_masuk_id'
        );
    }
}