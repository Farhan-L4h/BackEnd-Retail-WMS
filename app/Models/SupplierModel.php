<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierModel extends Model
{
    use HasFactory;

    protected $table = 'tb_supplier';
    protected $primarykey = 'id';

    protected $fillable = ['nama_supplier', 'kontak', 'alamat'];

    // Ganti default timestamps
    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_update';
    public function barang() :HasMany
    {
        return $this->hasMany(    AktivitasModel::class);

    }

}
