<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BarangModel extends Model
{
    use HasFactory;

    protected $table = 'tb_barang';
    protected $primarykey = 'id';

    protected $fillable = ['image', 'id_kategori', 'id_supplier','nama_barang', 'deskripsi','harga' ];

    // Ganti default timestamps
    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_update';

    public function kategori() :BelongsTo {
        return $this->belongsTo(KategoriModel::class, 'id_kategori', 'id');
    }

    public function supplier() :BelongsTo
    {
        return $this->belongsTo(SupplierModel::class,'id_supplier', 'id');
    }

    public function aktivitas() :HasMany
    {
        return $this->hasMany(    AktivitasModel::class);

    }

    public function pemindahan() :HasMany
    {
        return $this->hasMany(    PemindahanModel::class);

    }

    public function image(): Attribute {
        return Attribute::make(
            get: fn ($image) => $image ? url('/storage/' . $image) : null,
        );
    }

}
