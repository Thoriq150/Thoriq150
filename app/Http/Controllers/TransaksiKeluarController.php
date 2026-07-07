<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\TransaksiKeluar;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class TransaksiKeluarController extends Controller
{
    public function store(Request $request)
    {
        $transaksi = TransaksiKeluar::create([
            'tanggal' => now(),
            'total_jumlah' => $request->jumlah,
            'user_id' => Auth::id(),
        ]);

        DetailTransaksi::create([
            'transaksi_keluar_id' => $transaksi->transaksi_keluar_id,
            'barang_id' => $request->barang_id,
            'jumlah' => $request->jumlah,
        ]);

        // kurangi stok barang
        Product::where(
            'barang_id',
            $request->barang_id
        )->decrement(
            'stok',
            $request->jumlah
        );

        return back()->with(
            'success',
            'Transaksi berhasil'
        );
    }
}