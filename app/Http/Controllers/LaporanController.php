<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {

        // Mengambil data dari tb_barang dengan join ke tb_kategori, tb_supplier, dan tb_rak
        $data = DB::table('tb_barang')
            ->join('tb_kategori', 'tb_barang.id_kategori', '=', 'tb_kategori.id')
            ->join('tb_supplier', 'tb_barang.id_supplier', '=', 'tb_supplier.id')
            ->join('tb_aktivitas', 'tb_barang.id', '=', 'tb_aktivitas.id_barang')
            ->join('tb_rak', 'tb_aktivitas.id_rak', '=', 'tb_rak.id')
            ->join('tb_barang.*', 'tb_kategori.nama_kategori', 'tb_supplier.nama_supplier',)
            ->join('tb_pemindahan', 'tb_aktivitas.id', '=', 'tb_pemindahan.id_aktivitas')
            ->select(
                'tb_barang.*',
                'tb_kategori.nama_kategori',
                'tb_supplier.nama_supplier',
                'tb_supplier.kontak',
                'tb_rak.kode_rak',
                'tb_rak.nama_rak',
                'tb_aktivitas.status',
                'tb_aktivitas.alasan'
            )
            ->get();

        // Mengembalikan hasil dalam format JSON
        return response()->json($data);



        // // Ambil tanggal filter, jika tidak ada gunakan hari ini
        // $tanggalFilter = $request->input('tanggal', Carbon::today()->toDateString());

        // // Query barang dengan relasi rak dan supplier
        // $laporan = BarangModel::with(['rak', 'supplier'])
        //     ->orderBy('kategori', 'asc') // Urutkan berdasarkan kategori
        //     ->get();

        // // Proses data laporan untuk menghitung total stok dan total harga
        // $laporanData = $laporan->map(function ($barang) use ($tanggalFilter) {
        //     // Hitung total harga untuk setiap barang
        //     $totalHarga = $barang->stok * $barang->harga;

        //     return [
        //         'nama_barang' => $barang->nama,
        //         'kategori' => $barang->kategori,
        //         'satuan' => $barang->satuan,
        //         'stok' => $barang->stok,
        //         'expired' => $barang->expired, // Format expired date
        //         'lokasi' => $barang->rak->nama ?? 'N/A', // Nama rak
        //         'supplier' => $barang->supplier->nama ?? 'N/A', // Nama supplier
        //         'harga' => $barang->harga,
        //         'total_harga' => $totalHarga
        //     ];
        // });

        // // Hitung total stok dan total harga
        // $totalStok = $laporanData->sum('stok');
        // $totalHarga = $laporanData->sum('total_harga');

        // // Format response
        // return response()->json([
        //     'tanggal' => $tanggalFilter,
        //     'laporan' => $laporanData,
        //     'total_stok' => $totalStok,
        //     'total_harga' => $totalHarga,
        // ], 200);
    }
}
