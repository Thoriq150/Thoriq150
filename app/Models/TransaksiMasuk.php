<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransaksiMasuk extends Model
{
    use HasFactory;

    protected $table = 'transaksi_masuk';

    protected $primaryKey = 'transaksi_masuk_id';

    public $timestamps = false;

    protected $fillable = [
        'tanggal',
        'jumlah',
        'user_id'
    ];

    /**
     * Detail barang yang masuk
     */
    public function details()
    {
        return $this->hasMany(
            DetailTransaksi::class,
            'transaksi_masuk_id',
            'transaksi_masuk_id'
        );
    }

    /**
     * User yang melakukan transaksi
     */
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }
}