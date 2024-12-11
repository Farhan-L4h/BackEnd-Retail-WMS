<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivitasModel extends Model
{
    use HasFactory;

    protected $table = 'tb_aktivitas';
    protected $primaryKey = 'id';
    protected $fillable = ['id_barang','username', 'id_rak', 'exp_barang', 'jumlah_barang', 'harga_barang','total_harga','status','alasan'];

    // Ganti default timestamps
    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_update';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id'); // Pastikan key sesuai
    }

    public function rak(): BelongsTo
    {
        return $this->belongsTo(RakModel::class, 'id_rak', 'id'); // Pastikan key sesuai
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(BarangModel::class, 'id_barang', 'id'); // Pastikan key sesuai
    }

    public function pemindahan(): HasMany
    {
        return $this->hasMany(PemindahanModel::class);
    }
  }
?>
