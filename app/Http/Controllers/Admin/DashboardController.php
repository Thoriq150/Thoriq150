<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\TransaksiMasuk;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        // TOTAL DATA
        $categories = Category::count();

        $suppliers = Supplier::count();

        $products = Product::count();

        $customers = User::count();

        $jumlahPrediksi = DB::table('prediksi_stok')->count();
        // TOTAL BARANG KELUAR
        $transactions = DetailTransaksi::sum('jumlah');

        // TOTAL BARANG MASUK
        $transaksiMasuk = TransaksiMasuk::count();

        // TRANSAKSI BULAN INI
        $transactionThisMonth = DetailTransaksi::whereMonth(
            'created_at',
            date('m')
        )->sum('jumlah');

        // STOK MENIPIS
        $productsOutStock = Product::with('kategori')
            ->where('stok', '<=', 10)
            ->orderBy('stok', 'asc')
            ->paginate(5);

         // PREDIKSI STOK
        $prediksi = DB::table('prediksi_stok')
            ->join('barang', 'barang.barang_id', '=', 'prediksi_stok.barang_id')
            ->select(
                'prediksi_stok.*',
                'barang.nama_barang',
                'barang.stok'
            )
            ->orderByDesc('hasil_prediksi')
            ->get();

        // CHART BARANG POPULER
        $bestProduct = DB::table('detail_transaksi')
            ->selectRaw('
                barang.nama_barang as name,
                SUM(detail_transaksi.jumlah) as total
            ')
            ->join(
                'barang',
                'barang.barang_id',
                '=',
                'detail_transaksi.barang_id'
            )
            ->groupBy(
                'detail_transaksi.barang_id',
                'barang.nama_barang'
            )
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $label = [];
        $total = [];

        if ($bestProduct->count() > 0) {

            foreach ($bestProduct as $data) {

                $label[] = $data->name;

                $total[] = (int) $data->total;
            }

        } else {

            $label[] = 'Tidak Ada Data';

            $total[] = 0;
        }

        return view('admin.dashboard', compact(
            'categories',
            'suppliers',
            'products',
            'customers',
            'transactions',
            'transaksiMasuk',
            'transactionThisMonth',
            'productsOutStock',
            'label',
            'total',
            'jumlahPrediksi',
            'prediksi'
        ));
    }
}