<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\TransaksiMasuk;
use App\Models\TransaksiKeluar;
use App\Models\DetailTransaksi;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $userId = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan Dashboard
        |--------------------------------------------------------------------------
        */

        $totalTransaksiMasuk = TransaksiMasuk::where(
            'user_id',
            $userId
        )->count();

        $totalTransaksiKeluar = TransaksiKeluar::where(
            'user_id',
            $userId
        )->count();

        $totalBarangKeluar = DetailTransaksi::whereHas(
            'transaksiKeluar',
            function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }
        )->sum('jumlah');

        $totalBarangMasuk = DetailTransaksi::whereHas(
            'transaksiMasuk',
            function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }
        )->sum('jumlah');

        /*
        |--------------------------------------------------------------------------
        | Riwayat Transaksi Keluar Terbaru
        |--------------------------------------------------------------------------
        */

        $transactions = TransaksiKeluar::with('details.barang')
            ->where('user_id', $userId)
            ->latest('tanggal')
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Prediksi Barang Customer
        |--------------------------------------------------------------------------
        */

        $prediksi = DB::table('prediksi_stok')
            ->join(
                'barang',
                'barang.barang_id',
                '=',
                'prediksi_stok.barang_id'
            )
            ->join(
                'detail_transaksi',
                'detail_transaksi.barang_id',
                '=',
                'barang.barang_id'
            )
            ->join(
                'transaksi_keluar',
                'transaksi_keluar.transaksi_keluar_id',
                '=',
                'detail_transaksi.transaksi_keluar_id'
            )
            ->where(
                'transaksi_keluar.user_id',
                $userId
            )
            ->select(
                'prediksi_stok.*',
                'barang.nama_barang',
                'barang.stok'
            )
            ->distinct()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Hitung Status
        |--------------------------------------------------------------------------
        */

        $warning = 0;
        $restock = 0;

        foreach ($prediksi as $item) {

            if ($item->stok < $item->hasil_prediksi) {

                $item->status = 'Restock';
                $restock++;

            } elseif ($item->stok <= ($item->hasil_prediksi * 1.2)) {

                $item->status = 'Warning';
                $warning++;

            } else {

                $item->status = 'Aman';
            }
        }

        return view(
            'customer.dashboard',
            compact(
                'transactions',
                'totalTransaksiMasuk',
                'totalTransaksiKeluar',
                'totalBarangMasuk',
                'totalBarangKeluar',
                'prediksi',
                'warning',
                'restock'
            )
        );
    }
}