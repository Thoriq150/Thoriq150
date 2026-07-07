<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\TransaksiMasuk;
use App\Models\TransaksiKeluar;
use App\Models\DetailTransaksi;

use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->from;
        $toDate   = $request->to;

        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI MASUK
        |--------------------------------------------------------------------------
        */

        $transaksiMasuk = TransaksiMasuk::with([
                'details.barang',
                'user'
            ])
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('tanggal', [$fromDate, $toDate]);
            })
            ->orderByDesc('tanggal')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI KELUAR
        |--------------------------------------------------------------------------
        */

        $transaksiKeluar = TransaksiKeluar::with([
                'details.barang',
                'user'
            ])
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('tanggal', [$fromDate, $toDate]);
            })
            ->orderByDesc('tanggal')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RINGKASAN
        |--------------------------------------------------------------------------
        */

        $jumlahTransaksiMasuk = $transaksiMasuk->count();

        $jumlahTransaksiKeluar = $transaksiKeluar->count();

        // Total seluruh kuantitas barang masuk
        $totalMasuk = DetailTransaksi::join(
                'transaksi_masuk',
                'detail_transaksi.transaksi_masuk_id',
                '=',
                'transaksi_masuk.transaksi_masuk_id'
            )
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween(
                    'transaksi_masuk.tanggal',
                    [$fromDate, $toDate]
                );
            })
            ->sum('detail_transaksi.jumlah');

        // Total seluruh kuantitas barang keluar
        $totalKeluar = DetailTransaksi::join(
                'transaksi_keluar',
                'detail_transaksi.transaksi_keluar_id',
                '=',
                'transaksi_keluar.transaksi_keluar_id'
            )
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween(
                    'transaksi_keluar.tanggal',
                    [$fromDate, $toDate]
                );
            })
            ->sum('detail_transaksi.jumlah');

        /*
        |--------------------------------------------------------------------------
        | TOP 10 BARANG TERLARIS
        |--------------------------------------------------------------------------
        */

        $topBarang = DetailTransaksi::select(
                'barang.nama_barang',
                DB::raw('SUM(detail_transaksi.jumlah) as total_terjual')
            )
            ->join(
                'barang',
                'barang.barang_id',
                '=',
                'detail_transaksi.barang_id'
            )
            ->whereNotNull('detail_transaksi.transaksi_keluar_id')
            ->groupBy(
                'barang.barang_id',
                'barang.nama_barang'
            )
            ->orderByDesc('total_terjual')
            ->limit(10)
            ->get();

        return view(
            'admin.report.index',
            compact(
                'transaksiMasuk',
                'transaksiKeluar',
                'fromDate',
                'toDate',
                'jumlahTransaksiMasuk',
                'jumlahTransaksiKeluar',
                'totalMasuk',
                'totalKeluar',
                'topBarang'
            )
        );
    }
}