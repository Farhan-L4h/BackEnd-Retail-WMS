<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tanggal filter, jika tidak ada gunakan hari ini
        $tanggalFilter = $request->input('tanggal', Carbon::today()->toDateString());

        // Query untuk mengambil data barang, aktivitas, pemindahan, dan stok
        $data = DB::table('tb_barang')
            ->join('tb_kategori', 'tb_barang.id_kategori', '=', 'tb_kategori.id')
            ->join('tb_supplier', 'tb_barang.id_supplier', '=', 'tb_supplier.id')
            ->leftJoin('tb_aktivitas', 'tb_barang.id', '=', 'tb_aktivitas.id_barang')
            ->leftJoin('tb_rak', 'tb_aktivitas.id_rak', '=', 'tb_rak.id')
            ->leftJoin('tb_pemindahan', 'tb_aktivitas.id', '=', 'tb_pemindahan.id_aktivitas')
            ->select(
                'tb_barang.id as id_barang',
                'tb_barang.nama_barang',
                'tb_kategori.nama_kategori',
                'tb_supplier.nama_supplier',
                'tb_supplier.kontak as supplier_kontak',
                'tb_rak.kode_rak',
                'tb_rak.nama_rak',
                'tb_aktivitas.status',
                'tb_aktivitas.alasan',
                'tb_aktivitas.jumlah_barang',
                'tb_aktivitas.harga_barang',
                'tb_aktivitas.total_harga',
                'tb_barang.exp_barang',
                DB::raw('COALESCE(SUM(CASE WHEN tb_aktivitas.status = "masuk" THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN tb_aktivitas.status = "keluar" THEN tb_aktivitas.jumlah_barang ELSE 0 END), 0) AS stok_akhir'),
                DB::raw('SUM(CASE WHEN tb_pemindahan.id_aktivitas IS NOT NULL THEN tb_pemindahan.jumlah_pindah ELSE 0 END) AS jumlah_pemindahan')
            )
            ->groupBy(
                'tb_barang.id',
                'tb_barang.nama_barang',
                'tb_kategori.nama_kategori',
                'tb_supplier.nama_supplier',
                'tb_supplier.kontak',
                'tb_rak.kode_rak',
                'tb_rak.nama_rak',
                'tb_aktivitas.status',
                'tb_aktivitas.alasan',
                'tb_aktivitas.jumlah_barang',
                'tb_aktivitas.harga_barang',
                'tb_aktivitas.total_harga',
                'tb_barang.exp_barang'
            )
            ->when($tanggalFilter, function ($query, $tanggalFilter) {
                // Jika filter tanggal ada, ambil data berdasarkan tanggal
                return $query->whereDate('tb_aktivitas.created_at', '=', $tanggalFilter);
            })
            ->get();

        // Format laporan dengan informasi tambahan
        $laporanData = $data->map(function ($item) {
            return [
                'nama_barang' => $item->nama_barang,
                'kategori' => $item->nama_kategori,
                'supplier' => $item->nama_supplier,
                'kontak_supplier' => $item->supplier_kontak,
                'lokasi_rak' => $item->nama_rak,
                'kode_rak' => $item->kode_rak,
                'stok_akhir' => $item->stok_akhir,
                'jumlah_pemindahan' => $item->jumlah_pemindahan,
                'status' => $item->status,
                'alasan' => $item->alasan,
                'expired' => $item->exp_barang ? Carbon::parse($item->exp_barang)->format('d-m-Y') : 'N/A', // Format expired
                'total_harga' => $item->total_harga,
            ];
        });

        // Hitung total stok dan total harga
        $totalStok = $laporanData->sum('stok_akhir');
        $totalHarga = $laporanData->sum('total_harga');

        // Return data laporan dalam format JSON
        return response()->json([
            'tanggal' => $tanggalFilter,
            'laporan' => $laporanData,
            'total_stok' => $totalStok,
            'total_harga' => $totalHarga,
        ], 200);
    }
}
?>