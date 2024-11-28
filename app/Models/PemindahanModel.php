<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemindahanModel extends Model
{
    use HasFactory;

    protected $table = 'tb_pemindahan';
    protected $primarykey = 'id';

    protected $fillable = ['id_barang', 'id_rak_asal', 'id_rak_tujuan', 'jumlah_pindah', 'tanggal_pindah'];

    // Ganti default timestamps
    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_update';
    public function rak() :BelongsTo
    {
        return $this->belongsTo(RakModel::class);
    }

    public function barang() :BelongsTo
    {
        return $this->belongsTo(BarangModel::class);
    }

}