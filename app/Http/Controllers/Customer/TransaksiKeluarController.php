<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Models\TransaksiKeluar;
use App\Models\DetailTransaksi;

class TransaksiKeluarController extends Controller
{
    public function index()
    {
        $user = Auth::id();

        $transaksiKeluar = DetailTransaksi::with([
                'barang',
                'transaksiKeluar.user'
            ])
            ->whereNotNull('transaksi_keluar_id')
            ->whereHas('transaksiKeluar', function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->orderByDesc('detail_id')
            ->paginate(10);

        $grandTransaction = $transaksiKeluar->total();

        $grandQuantity = DetailTransaksi::whereNotNull('transaksi_keluar_id')
            ->whereHas('transaksiKeluar', function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->sum('jumlah');

        return view(
            'customer.transaksi_keluar.index',
            compact(
                'transaksiKeluar',
                'grandTransaction',
                'grandQuantity'
            )
        );
    }

    public function create()
    {
        $products = Product::where('stok', '>', 0)
            ->orderBy('nama_barang')
            ->get();

        return view(
            'customer.transaksi_keluar.create',
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

            if ($request->jumlah > $product->stok) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Jumlah barang keluar melebihi stok tersedia.'
                    );
            }

            $transaksi = TransaksiKeluar::create([
                'tanggal'      => $request->tanggal,
                'total_jumlah' => $request->jumlah,
                'user_id'      => Auth::id()
            ]);

            DetailTransaksi::create([
                'transaksi_keluar_id' => $transaksi->transaksi_keluar_id,
                'transaksi_masuk_id'  => null,
                'barang_id'           => $product->barang_id,
                'jumlah'              => $request->jumlah
            ]);

            $product->decrement('stok', $request->jumlah);

            DB::commit();

            return redirect()
                ->route('customer.transaksi-keluar.index')
                ->with(
                    'success',
                    'Transaksi barang keluar berhasil ditambahkan.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Terjadi kesalahan : ' . $e->getMessage()
                );
        }
    }
}