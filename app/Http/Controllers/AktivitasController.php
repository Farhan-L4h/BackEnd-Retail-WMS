<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AktivitasModel;
use App\Models\PemindahanModel;
use App\Models\BarangModel;
use Carbon\Carbon;

class AktivitasController extends Controller
{
    public function storeAktivitas(Request $request)
    {
        $request->validate([
            'id_barang' => 'required|exists:tb_barang,id',
            'id_user' => 'required|exists:users,id',
            'id_rak' => 'required|exists:tb_rak,id',
            'exp_barang' => 'nullable|date',
            'jumlah_barang' => 'required|integer|min:1',
            'harga_barang' => 'required|integer|min:0',
            'status' => 'required|in:masuk,keluar',
            'alasan' => 'nullable|in:diambil,return,dibuang',
        ]);

        // Total harga = jumlah x harga per barang
        $total_harga = $request->jumlah_barang * $request->harga_barang;

        // Update stok barang
        $barang = BarangModel::findOrFail($request->id_barang);
        if ($request->status === 'masuk') {
            $barang->stok += $request->jumlah_barang;
        } elseif ($request->status === 'keluar') {
            if ($barang->stok < $request->jumlah_barang) {
                return response()->json(['message' => 'Stok tidak mencukupi'], 400);
            }
            $barang->stok -= $request->jumlah_barang;
        }
        $barang->save();

        // Simpan aktivitas
        $aktivitas = AktivitasModel::create([
            'id_barang' => $request->id_barang,
            'id_user' => $request->id_user,
            'id_rak' => $request->id_rak,
            'exp_barang' => $request->exp_barang,
            'jumlah_barang' => $request->jumlah_barang,
            'harga_barang' => $request->harga_barang,
            'total_harga' => $total_harga,
            'status' => $request->status,
            'alasan' => $request->alasan,
            'tanggal_dibuat' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Aktivitas berhasil dicatat',
            'data' => $aktivitas,
        ], 201);
    }

    public function updateAktivitas(Request $request, $id)
{
    $request->validate([
        'id_barang' => 'required|exists:tb_barang,id',
        'id_user' => 'required|exists:users,id',
        'id_rak' => 'required|exists:tb_rak,id',
        'exp_barang' => 'nullable|date',
        'jumlah_barang' => 'required|integer|min:1',
        'harga_barang' => 'required|integer|min:0',
        'status' => 'required|in:masuk,keluar',
        'alasan' => 'nullable|in:diambil,return,dibuang',
    ]);

    $aktivitas = AktivitasModel::findOrFail($id);
    $barang = BarangModel::findOrFail($aktivitas->id_barang);

    // Reset stok barang berdasarkan aktivitas lama
    if ($aktivitas->status === 'masuk') {
        $barang->stok -= $aktivitas->jumlah_barang;
    } elseif ($aktivitas->status === 'keluar') {
        $barang->stok += $aktivitas->jumlah_barang;
    }

    // Update stok barang berdasarkan aktivitas baru
    if ($request->status === 'masuk') {
        $barang->stok += $request->jumlah_barang;
    } elseif ($request->status === 'keluar') {
        if ($barang->stok < $request->jumlah_barang) {
            return response()->json(['message' => 'Stok tidak mencukupi untuk diupdate'], 400);
        }
        $barang->stok -= $request->jumlah_barang;
    }
    $barang->save();

    // Update aktivitas
    $aktivitas->update([
        'id_barang' => $request->id_barang,
        'id_user' => $request->id_user,
        'id_rak' => $request->id_rak,
        'exp_barang' => $request->exp_barang,
        'jumlah_barang' => $request->jumlah_barang,
        'harga_barang' => $request->harga_barang,
        'total_harga' => $request->jumlah_barang * $request->harga_barang,
        'status' => $request->status,
        'alasan' => $request->alasan,
        'tanggal_update' => now(),
    ]);

    return response()->json([
        'message' => 'Aktivitas berhasil diperbarui',
        'data' => $aktivitas,
    ]);
}

public function destroyAktivitas($id)
{
    $aktivitas = AktivitasModel::findOrFail($id);
    $barang = BarangModel::findOrFail($aktivitas->id_barang);

    // Reset stok barang berdasarkan aktivitas yang dihapus
    if ($aktivitas->status === 'masuk') {
        $barang->stok -= $aktivitas->jumlah_barang;
    } elseif ($aktivitas->status === 'keluar') {
        $barang->stok += $aktivitas->jumlah_barang;
    }
    $barang->save();

    $aktivitas->delete();

    return response()->json([
        'message' => 'Aktivitas berhasil dihapus',
    ]);
}

public function show($id)
{
    $aktivitas = AktivitasModel::with(['barang', 'user', 'rak']) // Include relasi terkait
        ->findOrFail($id); // Cari data aktivitas berdasarkan ID

    return response()->json([
        'message' => 'Detail aktivitas ditemukan',
        'data' => $aktivitas,
    ], 200);
}


public function storePemindahan(Request $request)
{
    $request->validate([
        'id_barang' => 'required|exists:tb_barang,id',
        'id_rak_asal' => 'required|exists:tb_rak,id',
        'id_rak_tujuan' => 'required|exists:tb_rak,id',
        'jumlah_pindah' => 'required|integer|min:1',
    ]);

    // Validasi stok di rak asal
    $barang = BarangModel::findOrFail($request->id_barang);
    if ($barang->stok < $request->jumlah_pindah) {
        return response()->json(['message' => 'Stok tidak mencukupi untuk dipindahkan'], 400);
    }

    // Kurangi stok di rak asal dan tambahkan ke rak tujuan
    $barang->stok -= $request->jumlah_pindah;
    $barang->save();

    // Simpan pemindahan
    $pemindahan = PemindahanModel::create([
        'id_barang' => $request->id_barang,
        'id_rak_asal' => $request->id_rak_asal,
        'id_rak_tujuan' => $request->id_rak_tujuan,
        'jumlah_pindah' => $request->jumlah_pindah,
        'tanggal_dibuat' => now(),
    ]);

    return response()->json([
        'message' => 'Barang berhasil dipindahkan',
        'data' => $pemindahan,
    ], 201);
}

public function updatePemindahan(Request $request, $id)
{
    $request->validate([
        'id_barang' => 'required|exists:tb_barang,id',
        'id_rak_asal' => 'required|exists:tb_rak,id',
        'id_rak_tujuan' => 'required|exists:tb_rak,id',
        'jumlah_pindah' => 'required|integer|min:1',
    ]);

    $pemindahan = PemindahanModel::findOrFail($id);
    $barang = BarangModel::findOrFail($pemindahan->id_barang);

    // Reset stok barang berdasarkan pemindahan lama
    $barang->stok += $pemindahan->jumlah_pindah; // Tambah stok ke rak asal

    // Update stok barang berdasarkan pemindahan baru
    if ($barang->stok < $request->jumlah_pindah) {
        return response()->json(['message' => 'Stok tidak mencukupi untuk dipindahkan'], 400);
    }
    $barang->stok -= $request->jumlah_pindah; // Kurangi stok dari rak tujuan
    $barang->save();

    // Update pemindahan
    $pemindahan->update([
        'id_barang' => $request->id_barang,
        'id_rak_asal' => $request->id_rak_asal,
        'id_rak_tujuan' => $request->id_rak_tujuan,
        'jumlah_pindah' => $request->jumlah_pindah,
        'tanggal_update' => now(),
    ]);

    return response()->json([
        'message' => 'Pemindahan berhasil diperbarui',
        'data' => $pemindahan,
    ]);
}

public function destroyPemindahan($id)
{
    $pemindahan = PemindahanModel::findOrFail($id);
    $barang = BarangModel::findOrFail($pemindahan->id_barang);

    // Reset stok barang berdasarkan pemindahan yang dihapus
    $barang->stok += $pemindahan->jumlah_pindah; // Tambahkan stok kembali ke rak asal
    $barang->save();

    $pemindahan->delete();

    return response()->json([
        'message' => 'Pemindahan berhasil dihapus',
    ]);
}

}
