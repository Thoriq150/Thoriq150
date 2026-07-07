<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\TransaksiKeluar;
use App\Models\DetailTransaksi;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $fromDate = $request->from;
        $toDate   = $request->to;

        // Transaksi milik customer yang login
        $transaksiKeluar = TransaksiKeluar::with(
                'details.barang',
                'user'
            )
            ->where('user_id', $userId)
            ->when(
                $fromDate && $toDate,
                function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween(
                        'tanggal',
                        [$fromDate, $toDate]
                    );
                }
            )
            ->orderBy('tanggal', 'desc')
            ->get();

        // Statistik customer

        $jumlahTransaksi = $transaksiKeluar->count();

        $totalPembelian = $transaksiKeluar->sum('total_jumlah');

        $totalBarang = DetailTransaksi::whereHas(
                'transaksiKeluar',
                function ($query) use (
                    $userId,
                    $fromDate,
                    $toDate
                ) {
                    $query->where(
                        'user_id',
                        $userId
                    );

                    if ($fromDate && $toDate) {
                        $query->whereBetween(
                            'tanggal',
                            [$fromDate, $toDate]
                        );
                    }
                }
            )
            ->sum('jumlah');

        return view(
            'customer.report.index',
            compact(
                'transaksiKeluar',
                'fromDate',
                'toDate',
                'jumlahTransaksi',
                'totalPembelian',
                'totalBarang'
            )
        );
    }
}