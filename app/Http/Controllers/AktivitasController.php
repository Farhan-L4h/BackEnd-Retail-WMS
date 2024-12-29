<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AktivitasModel;
use App\Models\PemindahanModel;
use App\Models\BarangModel;
use App\Events\StokUpdated;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AktivitasController extends Controller
{
    public function indexAktivitas(Request $request)
    {
        // Ambil daftar aktivitas dengan relasi
        $aktivitas = AktivitasModel::with(['barang', 'user', 'rak'])->get();

        // Tambahkan informasi total barang masuk, keluar, dan stok untuk setiap aktivitas
        $aktivitas = $aktivitas->map(function ($item) {
            // Hitung total barang masuk dan keluar untuk setiap barang
            $stokData = BarangModel::selectRaw("
                COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'masuk' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS total_masuk,
                COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'keluar' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS total_keluar,
                COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'masuk' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'keluar' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS stok
            ")
            ->leftJoin('tb_aktivitas', 'tb_barang.id', '=', 'tb_aktivitas.id_barang')
            ->where('tb_barang.id', $item->id_barang)
            ->groupBy('tb_barang.id')
            ->first();

            // Menambahkan data stok ke dalam aktivitas
            $item->total_masuk = $stokData->total_masuk ?? 0;
            $item->total_keluar = $stokData->total_keluar ?? 0;
            $item->stok_terbaru = $stokData->stok ?? 0;

            return $item;
        });

        return response()->json([
            'message' => 'Daftar aktivitas berhasil diambil',
            'data' => $aktivitas,
        ], 200);
    }


    public function storeAktivitas(Request $request)
    {
        $request->validate([
            'id_barang' => 'required|exists:tb_barang,id',
            'username' => 'required',
            'id_rak' => 'required|exists:tb_rak,id',
            'exp_barang' => 'nullable|date',
            'jumlah_barang' => 'required|integer|min:1',
            'status' => 'required|in:masuk,keluar',
            'alasan' => 'nullable|in:diterima,diambil,return,dibuang',
        ]);

        DB::beginTransaction();
        try {
            // Ambil harga barang dari tabel tb_barang
            $barang = BarangModel::findOrFail($request->id_barang);
            $harga_barang = $barang->harga;

            // Total harga = jumlah x harga per barang
            $total_harga = $request->jumlah_barang * $harga_barang;

            // Hitung total masuk, total keluar, dan stok barang berdasarkan aktivitas sebelumnya
            $stokData = BarangModel::selectRaw("
                    COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'masuk' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS total_masuk,
                    COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'keluar' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS total_keluar,
                    COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'masuk' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'keluar' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS stok
                ")
                ->leftJoin('tb_aktivitas', 'tb_barang.id', '=', 'tb_aktivitas.id_barang')
                ->where('tb_barang.id', $request->id_barang)
                ->groupBy('tb_barang.id')
                ->first();

            // Ambil hasil query stok
            $total_masuk = $stokData->total_masuk ?? 0;
            $total_keluar = $stokData->total_keluar ?? 0;
            $stok = $stokData->stok ?? 0;

            // Validasi stok jika status keluar
            if ($request->status === 'keluar' && $stok < $request->jumlah_barang) {
                return response()->json(['message' => 'Stok barang tidak mencukupi'], 400);
            }

            // Update total masuk atau keluar sesuai dengan aktivitas baru
            if ($request->status === 'masuk') {
                $total_masuk += $request->jumlah_barang;
            } else {
                $total_keluar += $request->jumlah_barang;
            }

            // Hitung stok terbaru
            $stok_terbaru = $total_masuk - $total_keluar;

            // Tentukan batas stok minimum
            $threshold = 10;

            // Kirim event untuk broadcasting jika stok rendah
            if ($stok_terbaru < $threshold) {
                event(new StokUpdated($request->id_barang, $stok_terbaru)); // Mengirim event broadcast
            }

            // Simpan data aktivitas
            $aktivitas = AktivitasModel::create([
                'id_barang' => $request->id_barang,
                'username' => $request->username,
                'id_rak' => $request->id_rak,
                'exp_barang' => $request->exp_barang,
                'jumlah_barang' => $request->jumlah_barang,
                'harga_barang' => $harga_barang,
                'total_harga' => $total_harga,
                'status' => $request->status,
                'alasan' => $request->alasan,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Aktivitas berhasil ditambahkan',
                'data' => $aktivitas,
                'stok' => $stok_terbaru,
                'total_masuk' => $total_masuk,
                'total_keluar' => $total_keluar
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan aktivitas', 'error' => $e->getMessage()], 500);
        }
    }


    // Fungsi untuk menampilkan detail barang berdasarkan id_barang
    public function show($id)
    {
        // Ambil aktivitas terkait barang
        $aktivitas = AktivitasModel::with(['barang', 'user', 'rak'])->find($id);

        // Jika data tidak ditemukan, kembalikan respons error
        if (!$aktivitas) {
            return response()->json([
                'success' => false,
                'message' => 'Aktivitas tidak ditemukan',
                'data' => null,
            ], 404);
        }

        // Hitung total barang masuk, keluar, dan stok untuk barang terkait
        $stokData = BarangModel::selectRaw("
            COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'masuk' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS total_masuk,
            COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'keluar' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS total_keluar,
            COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'masuk' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'keluar' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS stok
        ")
        ->leftJoin('tb_aktivitas', 'tb_barang.id', '=', 'tb_aktivitas.id_barang')
        ->where('tb_barang.id', $aktivitas->id_barang)
        ->groupBy('tb_barang.id')
        ->first();

        // Menambahkan data stok ke dalam aktivitas
        $aktivitas->total_masuk = $stokData->total_masuk ?? 0;
        $aktivitas->total_keluar = $stokData->total_keluar ?? 0;
        $aktivitas->stok_terbaru = $stokData->stok ?? 0;

        // Jika data ditemukan, kembalikan respons sukses
        return response()->json([
            'success' => true,
            'message' => 'Detail aktivitas berhasil diambil',
            'data' => $aktivitas,
        ], 200);
    }

    public function updateAktivitas(Request $request, $id)
    {
        $request->validate([
            'id_barang' => 'required|exists:tb_barang,id',
            'username' => 'required',
            'id_rak' => 'required|exists:tb_rak,id',
            'exp_barang' => 'nullable|date',
            'jumlah_barang' => 'required|integer|min:1',
            'harga_barang' => 'required|integer|min:0',
            'status' => 'required|in:masuk,keluar',
            'alasan' => 'nullable|in:diterima,diambil,return,dibuang',
        ]);

        DB::beginTransaction();
        try {
            // Ambil aktivitas yang akan diupdate
            $aktivitas = AktivitasModel::findOrFail($id);

            // Ambil harga barang dari tabel tb_barang
            $barang = BarangModel::findOrFail($request->id_barang);
            $harga_barang = $barang->harga;

            // Total harga = jumlah x harga per barang
            $total_harga = $request->jumlah_barang * $harga_barang;

            // Hitung total barang masuk dan keluar berdasarkan aktivitas sebelumnya
            $stokData = BarangModel::selectRaw("
                COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'masuk' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS total_masuk,
                COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'keluar' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS total_keluar,
                COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'masuk' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'keluar' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS stok
            ")
            ->leftJoin('tb_aktivitas', 'tb_barang.id', '=', 'tb_aktivitas.id_barang')
            ->where('tb_barang.id', $request->id_barang)
            ->groupBy('tb_barang.id')
            ->first();

            // Ambil hasil query stok
            $total_masuk = $stokData->total_masuk ?? 0;
            $total_keluar = $stokData->total_keluar ?? 0;
            $stok = $stokData->stok ?? 0;

            // Validasi stok jika status keluar
            if ($request->status === 'keluar' && $stok < $request->jumlah_barang) {
                return response()->json(['message' => 'Stok barang tidak mencukupi'], 400);
            }

            // Kembalikan stok dari aktivitas lama (sebelum update)
            if ($aktivitas->status === 'masuk') {
                $total_masuk -= $aktivitas->jumlah_barang;
            } elseif ($aktivitas->status === 'keluar') {
                $total_keluar -= $aktivitas->jumlah_barang;
            }

            // Tambahkan stok berdasarkan data baru
            if ($request->status === 'masuk') {
                $total_masuk += $request->jumlah_barang;
            } elseif ($request->status === 'keluar') {
                $total_keluar += $request->jumlah_barang;
            }

            // Hitung stok terbaru
            $stok_terbaru = $total_masuk - $total_keluar;

            // Tentukan batas stok minimum
            $threshold = 10;

            // Kirim event untuk broadcasting dan notifikasi stok rendah jika perlu
            if ($stok_terbaru < $threshold) {
                event(new StokUpdated($request->id_barang, $stok_terbaru));  // Mengirim event broadcast
            }

            // Update data aktivitas
            $aktivitas->update([
                'id_barang' => $request->id_barang,
                'username' => $request->username,
                'id_rak' => $request->id_rak,
                'exp_barang' => $request->exp_barang,
                'jumlah_barang' => $request->jumlah_barang,
                'harga_barang' => $harga_barang,
                'total_harga' => $total_harga,
                'status' => $request->status,
                'alasan' => $request->alasan,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Aktivitas berhasil diperbarui',
                'data' => $aktivitas,
                'stok_terbaru' => $stok_terbaru, // Stok terbaru barang
                'total_masuk' => $total_masuk,
                'total_keluar' => $total_keluar
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat memperbarui aktivitas', 'error' => $e->getMessage()], 500);
        }
    }



    public function destroyAktivitas(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Ambil aktivitas yang akan dihapus
            $aktivitas = AktivitasModel::findOrFail($id);

            // Ambil barang terkait untuk mengupdate stok
            $barang = BarangModel::with(['aktivitas'])
                ->where('id', $aktivitas->id_barang)
                ->lockForUpdate() // Mengunci baris barang untuk update transaksi
                ->first();

            // Hitung stok barang berdasarkan aktivitas sebelumnya
            $stok = $barang->aktivitas->reduce(function ($carry, $aktivitasItem) {
                if ($aktivitasItem->status === 'masuk') {
                    return $carry + $aktivitasItem->jumlah_barang;
                }
                return $carry - $aktivitasItem->jumlah_barang;
            }, 0);

            // Pastikan stok memiliki nilai default jika null
            $stok = $stok ?? 0;

            // Tentukan stok setelah penghapusan
            if ($aktivitas->status === 'keluar') {
                // Tambahkan kembali stok jika aktivitas keluar dihapus
                $stok += $aktivitas->jumlah_barang;
            } elseif ($aktivitas->status === 'masuk') {
                // Kurangkan stok jika aktivitas masuk dihapus
                $stok -= $aktivitas->jumlah_barang;
            }

            // Tentukan batas stok minimum
            $threshold = 10;

            // Kirim event untuk broadcasting dan notifikasi stok rendah jika perlu
            if ($stok < $threshold) {
                event(new StokUpdated($barang->id, $stok)); // Mengirim event broadcast
            }

            // Pastikan stok tidak negatif setelah penghapusan
            if ($stok < 0) {
                return response()->json(['message' => 'Stok barang tidak valid setelah aktivitas ini dihapus'], 400);
            }

            // Hapus aktivitas
            $aktivitas->delete();

            DB::commit();

            return response()->json([
                'message' => 'Aktivitas berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus aktivitas', 'error' => $e->getMessage()], 500);
        }
    }

    // Chart SUPPLIER
    public function getChartData()
    {
        // Mengambil total barang masuk dan keluar
        $chartData = AktivitasModel::selectRaw('status, SUM(jumlah_barang) as total')
            ->groupBy('status')
            ->get();

        return response()->json($chartData);
    }

    public function indexPemindahan()
    {
        // Menggunakan join dengan alias untuk rak asal dan rak tujuan
        $pemindahan = DB::table('tb_pemindahan')
            ->join('tb_aktivitas', 'tb_pemindahan.id_aktivitas', '=', 'tb_aktivitas.id')
            ->join('tb_barang', 'tb_aktivitas.id_barang', '=', 'tb_barang.id') // Join dengan tb_barang
            ->join('tb_rak as rak_asal', 'tb_pemindahan.id_rak_asal', '=', 'rak_asal.id')
            ->join('tb_rak as rak_tujuan', 'tb_pemindahan.id_rak_tujuan', '=', 'rak_tujuan.id')
            ->select(
                'tb_pemindahan.*',
                'tb_aktivitas.id_barang',
                'tb_barang.nama_barang as nama_barang', // Menambahkan nama_barang dari tb_barang
                'rak_asal.nama_rak as nama_rak_asal',
                'rak_tujuan.nama_rak as nama_rak_tujuan'
            )
            ->get();

        return response()->json([
            'message' => 'Daftar pemindahan berhasil diambil',
            'data' => $pemindahan,
        ], 200);
    }


    public function storePemindahan(Request $request)
    {
        $request->validate([
            'id_aktivitas' => 'required|exists:tb_aktivitas,id',
            'id_rak_asal' => 'required|exists:tb_rak,id',
            'id_rak_tujuan' => 'required|exists:tb_rak,id|different:id_rak_asal',
        ]);

        DB::beginTransaction();
        try {

            // Validasi bahwa aktivitas dan rak asal sesuai
            $aktivitas = DB::table('tb_aktivitas')
                ->where('id', $request->id_aktivitas)
                ->first();

            if (!$aktivitas) {
                return response()->json(['message' => 'Aktivitas tidak valid'], 404);
            }

            // Validasi bahwa rak asal ada
            $rakAsal = DB::table('tb_rak')->where('id', $request->id_rak_asal)->first();
            if (!$rakAsal) {
                return response()->json(['message' => 'Rak asal tidak valid'], 404);
            }

            // Validasi bahwa rak tujuan ada
            $rakTujuan = DB::table('tb_rak')->where('id', $request->id_rak_tujuan)->first();
            if (!$rakTujuan) {
                return response()->json(['message' => 'Rak tujuan tidak valid'], 404);
            }

            // Simpan data pemindahan
            $pemindahan = PemindahanModel::create([
                'id_aktivitas' => $request->id_aktivitas,
                'id_rak_asal' => $request->id_rak_asal,
                'id_rak_tujuan' => $request->id_rak_tujuan,
                'tanggal_dibuat' => now(),
                'tanggal_update' => now(),
            ]);

            // Update id_rak di tabel tb_aktivitas_barang
            DB::table('tb_aktivitas')
                ->where('id', $request->id_aktivitas)
                ->update(['id_rak' => $request->id_rak_tujuan]);

            DB::commit();

            return response()->json([
                'message' => 'Data pemindahan berhasil disimpan dan id_rak diperbarui',
                'data' => $pemindahan,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function destroyPemindahan($id)
    {
        DB::beginTransaction();
        try {
            // Hapus data pemindahan
            $pemindahan = PemindahanModel::findOrFail($id);
            $pemindahan->delete();

            DB::commit();

            return response()->json([
                'message' => 'Data pemindahan berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus data', 'error' => $e->getMessage()], 500);
        }
    }


    public function getDashboardStats()
    {
        // Hitung ulang total stok, barang masuk, dan keluar
        $stok = BarangModel::select('tb_barang.id', 'tb_barang.nama_barang')
            ->selectRaw("COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'masuk' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS total_masuk")
            ->selectRaw("COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'keluar' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS total_keluar")
            ->selectRaw("COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'masuk' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) -
                        COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'keluar' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS stok")
            ->leftJoin('tb_aktivitas', 'tb_barang.id', '=', 'tb_aktivitas.id_barang')
            ->groupBy('tb_barang.id')
            ->get();

        $totalBarangMasuk = $stok->sum('total_masuk');
        $totalBarangKeluar = $stok->sum('total_keluar');
        $totalStok = $stok->sum('stok');

        return response()->json([
            'total_barang_masuk' => $totalBarangMasuk,
            'total_barang_keluar' => $totalBarangKeluar,
            'total_stok' => $totalStok
        ]);
    }

    public function buangBarang(Request $request, $id)
    {
        $validated = $request->validate([
            'id_barang' => 'required|exists:tb_barang,id',
            'username' => 'required|string',
            'id_rak' => 'required|exists:tb_rak,id',
            'exp_barang' => 'nullable',
            'jumlah_barang' => 'required|integer|min:1',
            'status' => 'required|in:keluar',
            'alasan' => 'nullable|in:return,dibuang',
        ]);


        DB::beginTransaction();

        try {
            // Ambil aktivitas berdasarkan ID
            $aktivitas = AktivitasModel::findOrFail($id);

            // Ambil barang berdasarkan ID
            $barang = BarangModel::findOrFail($validated['id_barang']);

            // Hitung stok terakhir berdasarkan aktivitas sebelumnya
            $stok = BarangModel::selectRaw("
                COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'masuk' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN tb_aktivitas.status = 'keluar' THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS stok
            ")
            ->leftJoin('tb_aktivitas', 'tb_barang.id', '=', 'tb_aktivitas.id_barang')
            ->where('tb_barang.id', $validated['id_barang'])
            ->groupBy('tb_barang.id')
            ->value('stok');

            $stok_terakhir = $stok ?? 0;

            // Cek apakah stok mencukupi
            if ($stok_terakhir <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok barang sudah habis.',
                ], 400);
            }

            // Cek apakah jumlah barang yang akan dibuang melebihi stok tersedia
            if ($validated['jumlah_barang'] > $stok_terakhir) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah barang yang dibuang melebihi stok tersedia.',
                ], 400);
            }

            // Perbarui data aktivitas
            $aktivitas->update([
                'id_barang' => $validated['id_barang'],
                'username' => $validated['username'],
                'id_rak' => $validated['id_rak'],
                'status' => $validated['status'],
                'jumlah_barang' => $stok_terakhir,
                'exp_barang' => null,
                'alasan' => $validated['alasan'],
                'harga_barang' => $barang->harga,
                'total_harga' => $stok_terakhir * $barang->harga,
                'tanggal_update' => now(),
            ]);

           // Update stok barang dan exp_barang menjadi null
            $barang->update([
                'stok' => max($stok_terakhir - $validated['jumlah_barang'], 0), // Pastikan stok tidak negatif
                'exp_barang' => $stok_terakhir - $validated['jumlah_barang'] === 0 ? null : $barang->exp_barang, // Set null jika stok habis
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aktivitas berhasil diperbarui.',
                'data' => $aktivitas,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui aktivitas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
