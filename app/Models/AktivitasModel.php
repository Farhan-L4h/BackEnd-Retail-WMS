<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivitasModel extends Model
{
    use HasFactory;

    protected $table = 'tb_aktivitas';
    protected $primaryKey = 'id';

    protected $fillable = ['id_barang','id_user', 'id_rak', 'exp_barang', 'jumlah_barang', 'harga_barang','total_harga','status','alasan'];

    // Ganti default timestamps
    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_update';

    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rak() :BelongsTo
    {
        return $this->belongsTo(RakModel::class);
    }

    public function barang() :BelongsTo
    {
        return $this->belongsTo(BarangModel::class);
    }
}
