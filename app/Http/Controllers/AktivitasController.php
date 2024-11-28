<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AktivitasModel;
use App\Models\PemindahanModel;
use App\Models\BarangModel;
use Carbon\Carbon;

class AktivitasController extends Controller
{
    public function store(Request $request)
    {
        // Validasi data
        $request->validate([
            'id_barang' => 'required|exists:tb_barang,id',
            'id_user' => 'required|exists:users,id',
            // ... validasi field lainnya
        ]);

        // Cek jenis aktivitas (masuk atau keluar)
        if ($request->status === 'keluar') {
            // Buat record pemindahan jika status keluar
            PemindahanModel::create([
                'id_barang' => $request->id_barang,
                'id_rak_asal' => $request->id_rak_asal,
                'id_rak_tujuan' => $request->id_rak_tujuan,
                'jumlah_pindah' => $request->jumlah_barang
            ]);
        }

        // Buat record aktivitas
        AktivitasModel::create($request->all());

        // Update stok barang
        $barang = BarangModel::find($request->id_barang);
        if ($request->status === 'masuk') {
            $barang->jumlah_stok += $request->jumlah_barang;
        } else {
            $barang->jumlah_stok -= $request->jumlah_barang;
        }
        $barang->save();

        // Cek tanggal kadaluarsa
        if ($barang->exp_barang && Carbon::now()->greaterThan($barang->exp_barang)) {
            // Tandai barang sebagai expired
            $barang->status = 'expired';
            $barang->save();
        }

        // Cek stok rendah
        if ($barang->jumlah_stok <= 10) {
            // Kirim notifikasi atau lakukan tindakan lain
            // ...
        }

        return response()->json(['message' => 'Aktivitas berhasil ditambahkan']);
    }
}
