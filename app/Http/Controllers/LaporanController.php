<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangModel;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tanggal filter, jika tidak ada gunakan hari ini
        $tanggalFilter = $request->input('tanggal', Carbon::today()->toDateString());

        // Query barang dengan relasi rak dan supplier
        $laporan = BarangModel::with(['rak', 'supplier'])
            ->orderBy('kategori', 'asc') // Urutkan berdasarkan kategori
            ->get();

        // Proses data laporan untuk menghitung total stok dan total harga
        $laporanData = $laporan->map(function ($barang) use ($tanggalFilter) {
            // Hitung total harga untuk setiap barang
            $totalHarga = $barang->stok * $barang->harga;

            return [
                'nama_barang' => $barang->nama,
                'kategori' => $barang->kategori,
                'satuan' => $barang->satuan,
                'stok' => $barang->stok,
                'expired' => $barang->expired, // Format expired date
                'lokasi' => $barang->rak->nama ?? 'N/A', // Nama rak
                'supplier' => $barang->supplier->nama ?? 'N/A', // Nama supplier
                'harga' => $barang->harga,
                'total_harga' => $totalHarga
            ];
        });

        // Hitung total stok dan total harga
        $totalStok = $laporanData->sum('stok');
        $totalHarga = $laporanData->sum('total_harga');

        // Format response
        return response()->json([
            'tanggal' => $tanggalFilter,
            'laporan' => $laporanData,
            'total_stok' => $totalStok,
            'total_harga' => $totalHarga,
        ], 200);
    }
}
