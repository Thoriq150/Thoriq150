<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PrediksiController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Data Tabel (Pagination)
        |--------------------------------------------------------------------------
        */

        $prediksi = DB::table('prediksi_stok')
            ->join('barang', 'barang.barang_id', '=', 'prediksi_stok.barang_id')
            ->select(
                'prediksi_stok.*',
                'barang.nama_barang',
                'barang.stok'
            )
            ->orderBy('barang.nama_barang')
            ->paginate(10);

        /*
        |--------------------------------------------------------------------------
        | Seluruh Data (Dashboard & Grafik)
        |--------------------------------------------------------------------------
        */

        $allPrediksi = DB::table('prediksi_stok')
            ->join('barang', 'barang.barang_id', '=', 'prediksi_stok.barang_id')
            ->select(
                'prediksi_stok.*',
                'barang.nama_barang',
                'barang.stok'
            )
            ->orderBy('barang.nama_barang')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Threshold Warning (20%)
        |--------------------------------------------------------------------------
        */

        $batasWarning = 1.2;

        /*
        |--------------------------------------------------------------------------
        | Ringkasan Dashboard
        |--------------------------------------------------------------------------
        */

        $aman = 0;
        $warning = 0;
        $restock = 0;

        foreach ($allPrediksi as $item) {

            $item->batas_stok = $item->stok - $item->hasil_prediksi;

            if ($item->stok < $item->hasil_prediksi) {

                $item->status = 'Restock';
                $restock++;

            } elseif ($item->stok <= ($item->hasil_prediksi * $batasWarning)) {

                $item->status = 'Warning';
                $warning++;

            } else {

                $item->status = 'Aman';
                $aman++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Status Untuk Data Pagination
        |--------------------------------------------------------------------------
        */

       foreach ($prediksi as $item) {

    $item->batas_stok = $item->stok - $item->hasil_prediksi;

    if ($item->stok < $item->hasil_prediksi) {

        $item->status = 'Restock';

    } elseif ($item->stok <= ($item->hasil_prediksi * $batasWarning)) {

        $item->status = 'Warning';

    } else {

        $item->status = 'Aman';
    }
}

        $totalBarang = $allPrediksi->count();

        /*
        |--------------------------------------------------------------------------
        | Data Grafik
        |--------------------------------------------------------------------------
        */

        $labelChart = [];
        $stokChart = [];
        $prediksiChart = [];

        foreach ($allPrediksi as $item) {

            $labelChart[] = $item->nama_barang;
            $stokChart[] = (int) $item->stok;
            $prediksiChart[] = (int) $item->hasil_prediksi;
        }

        return view('admin.prediksi.index', compact(
            'prediksi',
            'aman',
            'warning',
            'restock',
            'totalBarang',
            'labelChart',
            'stokChart',
            'prediksiChart'
        ));
    }

    public function generate()
    {
        /*
        |--------------------------------------------------------------------------
        | Jalankan Python
        |--------------------------------------------------------------------------
        */

        $pythonFile = base_path('ai/python_predict.py');

        shell_exec("python \"$pythonFile\"");

        return redirect()
            ->route('admin.prediksi')
            ->with('success', 'Prediksi berhasil diperbarui.');
    }
}