<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrediksiController extends Controller
{
    public function index()
    {
        $prediksi = DB::table('prediksi_stok')
            ->join('barang', 'barang.barang_id', '=', 'prediksi_stok.barang_id')
            ->join('detail_transaksi', 'detail_transaksi.barang_id', '=', 'barang.barang_id')
            ->join(
                'transaksi_keluar',
                'transaksi_keluar.transaksi_keluar_id',
                '=',
                'detail_transaksi.transaksi_keluar_id'
            )
            ->where('transaksi_keluar.user_id', Auth::id())
            ->select(
                'prediksi_stok.*',
                'barang.nama_barang',
                'barang.stok'
            )
            ->distinct()
            ->orderBy('barang.nama_barang')
            ->paginate(10);

        $batasWarning = 1.2;

        foreach ($prediksi as $item) {

            if ($item->stok < $item->hasil_prediksi) {

                $item->status = 'Restock';

            } elseif ($item->stok <= ($item->hasil_prediksi * $batasWarning)) {

                $item->status = 'Warning';

            } else {

                $item->status = 'Aman';
            }
        }

        return view(
            'customer.prediksi.index',
            compact('prediksi')
        );
    }
}