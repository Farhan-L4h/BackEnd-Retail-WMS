<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AktivitasModel;
use App\Models\PemindahanModel;
use App\Models\BarangModel;
use Carbon\Carbon;
use App\Events\StokUpdated;
use Illuminate\Support\Facades\DB;

class AktivitasController extends Controller
{
    public function indexAktivitas(Request $request)
    {
        // Ambil daftar aktivitas dengan relasi
        $aktivitas = AktivitasModel::with(['barang', 'user', 'rak'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status); // Filter berdasarkan status (masuk/keluar)
            })
            ->orderBy('tanggal_dibuat', 'desc') // Urutkan berdasarkan tanggal terbaru
            ->paginate(10); // Pagination, 10 data per halaman

        return response()->json([
            'message' => 'Daftar aktivitas berhasil diambil',
            'data' => $aktivitas,
        ], 200);
    }

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
        'alasan' => 'nullable|in:diterima,diambil,return,dibuang',
    ]);

    // Total harga = jumlah x harga per barang
    $total_harga = $request->jumlah_barang * $request->harga_barang;

    // Update stok barang
    $barang = BarangModel::findOrFail($request->id_barang);

    // Hitung stok berdasarkan status
    if ($request->status === 'masuk') {
        $barang->stok += $request->jumlah_barang;
    } elseif ($request->status === 'keluar') {
        if ($barang->stok < $request->jumlah_barang) {
            return response()->json(['message' => 'Stok tidak mencukupi'], 400);
        }
        $barang->stok -= $request->jumlah_barang;
    }

    $barang->save();

    // Hitung stok terbaru
    $stok = DB::table('tb_aktivitas')
        ->selectRaw("
            SUM(CASE WHEN status = 'masuk' THEN jumlah_barang ELSE 0 END) -
            SUM(CASE WHEN status = 'keluar' THEN jumlah_barang ELSE 0 END) as stok
        ")
        ->where('id_barang', $request->id_barang)
        ->value('stok');

    // Tentukan batas stok minimum
    $threshold = 10;  // Misalnya, batas stok rendah adalah 10 unit

    // Kirim event untuk broadcasting dan notifikasi stok rendah jika perlu
    if ($stok < $threshold) {
        event(new StokUpdated($request->id_barang, $stok));  // Mengirim event broadcast
    }

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

    // Fungsi untuk menampilkan detail aktivitas berdasarkan id_barang
    public function show($id)
    {
        // Ambil aktivitas berdasarkan id_barang
        $aktivitas = DB::table('tb_aktivitas')
            ->where('id_barang', $id)
            ->select('id', 'id_barang', 'status', 'jumlah_barang', 'tanggal', 'keterangan')
            ->orderBy('tanggal', 'desc') // Mengurutkan berdasarkan tanggal terbaru
            ->get();

        // Jika tidak ada aktivitas untuk barang tersebut
        if ($aktivitas->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada aktivitas untuk barang ini.',
            ], 404);
        }

        // Mengirimkan data aktivitas
        return response()->json([
            'message' => 'Detail aktivitas berhasil diambil',
            'data' => $aktivitas,
        ], 200);
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
        'alasan' => 'nullable|in:diterima,diambil,return,dibuang',
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

    // Hitung stok terbaru
    $stok = DB::table('tb_aktivitas')
        ->selectRaw("
            SUM(CASE WHEN status = 'masuk' THEN jumlah_barang ELSE 0 END) -
            SUM(CASE WHEN status = 'keluar' THEN jumlah_barang ELSE 0 END) as stok
        ")
        ->where('id_barang', $request->id_barang)
        ->value('stok');

    // Tentukan batas stok minimum
    $threshold = 10;  // Misalnya, batas stok rendah adalah 10 unit

    // Kirim event untuk broadcasting dan notifikasi stok rendah jika perlu
    if ($stok < $threshold) {
        event(new StokUpdated($request->id_barang, $stok));  // Mengirim event broadcast
    }

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

    // Hitung stok terbaru
    $stok = DB::table('tb_aktivitas')
        ->selectRaw("
            SUM(CASE WHEN status = 'masuk' THEN jumlah_barang ELSE 0 END) -
            SUM(CASE WHEN status = 'keluar' THEN jumlah_barang ELSE 0 END) as stok
        ")
        ->where('id_barang', $barang->id)
        ->value('stok');

    // Tentukan batas stok minimum
    $threshold = 10;  // Misalnya, batas stok rendah adalah 10 unit

    // Kirim event untuk broadcasting dan notifikasi stok rendah jika perlu
    if ($stok < $threshold) {
        event(new StokUpdated($barang->id, $stok));  // Mengirim event broadcast
    }

    // Trigger event stok diperbarui
    event(new StokUpdated($barang->id, $stok));

    $aktivitas->delete();

    return response()->json([
        'message' => 'Aktivitas berhasil dihapus',
    ]);
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

        // Hitung stok berdasarkan aktivitas (masuk dan keluar)
        $stok = DB::table('tb_aktivitas')
            ->selectRaw("
                SUM(CASE WHEN status = 'masuk' THEN jumlah_barang ELSE 0 END) -
                SUM(CASE WHEN status = 'keluar' THEN jumlah_barang ELSE 0 END) as stok
            ")
            ->where('id_barang', $request->id_barang)
            ->value('stok');

        // Trigger event stok diperbarui
        event(new StokUpdated($request->id_barang, $stok));

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

        // Hitung stok berdasarkan aktivitas (masuk dan keluar)
        $stok = DB::table('tb_aktivitas')
            ->selectRaw("
                SUM(CASE WHEN status = 'masuk' THEN jumlah_barang ELSE 0 END) -
                SUM(CASE WHEN status = 'keluar' THEN jumlah_barang ELSE 0 END) as stok
            ")
            ->where('id_barang', $request->id_barang)
            ->value('stok');

        // Trigger event stok diperbarui
        event(new StokUpdated($request->id_barang, $stok));

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

        // Hitung stok berdasarkan aktivitas (masuk dan keluar)
        $stok = DB::table('tb_aktivitas')
            ->selectRaw("
                SUM(CASE WHEN status = 'masuk' THEN jumlah_barang ELSE 0 END) -
                SUM(CASE WHEN status = 'keluar' THEN jumlah_barang ELSE 0 END) as stok
            ")
            ->where('id_barang', $barang->id)
            ->value('stok');

        // Trigger event stok diperbarui
        event(new StokUpdated($barang->id, $stok));

        return response()->json([
            'message' => 'Pemindahan berhasil dihapus',
        ]);
    }
}
