<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\TransaksiKeluar;
use App\Models\DetailTransaksi;
use App\Http\Controllers\Controller;

class TransaksiKeluarController extends Controller
{
    public function index()
    {
        $transactions = TransaksiKeluar::with('details.barang',  'user')
                            ->orderBy(
                                'transaksi_keluar_id',
                                'desc'
                            )
                            ->paginate(10);

        $grandQuantity = DetailTransaksi::sum('jumlah');

        return view(
            'admin.transaksi_keluar.index',
            compact(
                'transactions',
                'grandQuantity'
            )
        );
    }
    
}