<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKeluar extends Model
{
    protected $table = 'transaksi_keluar';

    protected $primaryKey = 'transaksi_keluar_id';

    public $timestamps = false;

    protected $fillable = [
        'tanggal',
        'total_jumlah',
        'user_id'
    ];

    public function details()
    {
        return $this->hasMany(
            DetailTransaksi::class,
            'transaksi_keluar_id',
            'transaksi_keluar_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }
}