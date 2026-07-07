<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\TransaksiMasuk;
use App\Http\Controllers\Controller;

class TransaksiMasukController extends Controller
{
    public function index()
    {
        $transaksiMasuk = TransaksiMasuk::with('user')
            ->orderBy('transaksi_masuk_id', 'desc')
            ->paginate(10);

        $totalBarangMasuk = TransaksiMasuk::sum('total_jumlah');

        return view(
            'admin.transaksi_masuk.index',
            compact(
                'transaksiMasuk',
                'totalBarangMasuk'
            )
        );
    }
}