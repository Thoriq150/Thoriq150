<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Models\TransaksiMasuk;
use App\Models\DetailTransaksi;

class TransaksiMasukController extends Controller
{
    public function index()
    {
        $transaksiMasuk = DetailTransaksi::with([
                'barang',
                'transaksiMasuk.user'
            ])
            ->whereNotNull('transaksi_masuk_id')
            ->whereHas('transaksiMasuk', function ($query) {

                $query->where('user_id', Auth::id());

            })
            ->orderByDesc('detail_id')
            ->paginate(10);

        return view(
            'customer.transaksi_masuk.index',
            compact('transaksiMasuk')
        );
    }

    public function create()
    {
        $products = Product::orderBy('nama_barang')->get();

        return view(
            'customer.transaksi_masuk.create',
            compact('products')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'   => 'required|date',
            'barang_id' => 'required|exists:barang,barang_id',
            'jumlah'    => 'required|integer|min:1'
        ]);

        DB::beginTransaction();

        try {

            $product = Product::findOrFail($request->barang_id);

            $transaksi = TransaksiMasuk::create([
                'tanggal'      => $request->tanggal,
                'total_jumlah' => $request->jumlah,
                'user_id'      => Auth::id()
            ]);

            DetailTransaksi::create([
                'transaksi_masuk_id'  => $transaksi->transaksi_masuk_id,
                'transaksi_keluar_id' => null,
                'barang_id'           => $product->barang_id,
                'jumlah'              => $request->jumlah
            ]);

            $product->increment('stok', $request->jumlah);

            DB::commit();

            return redirect()
                ->route('customer.transaksi-masuk.index')
                ->with(
                    'success',
                    'Transaksi barang masuk berhasil ditambahkan.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Terjadi kesalahan : '.$e->getMessage()
                );
        }
    }
}