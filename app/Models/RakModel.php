<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RakModel extends Model
{
    use HasFactory;

    protected $table = 'tb_rak';
    protected $primarykey = 'id';

    protected $fillable = ['kode_rak', 'nama_rak','lokasi_rak'];

    // Ganti default timestamps
    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_update';
    public function aktivitas() :HasMany
    {
        return $this->hasMany(    AktivitasModel::class);

    }

     public function pemindahan() :HasMany
    {
        return $this->hasMany(    PemindahanModel::class);

    }

}