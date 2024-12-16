<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BarangModel;
use Carbon\Carbon;

class ClearExpiredItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'barang:clear-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan barang expired beserta aktivitasnya';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString(); // Tanggal hari ini

        // Cari barang yang sudah expired
        $expiredBarang = BarangModel::select('tb_barang.id')
            ->leftJoin('tb_aktivitas', 'tb_barang.id', '=', 'tb_aktivitas.id_barang')
            ->where('tb_aktivitas.exp_barang', '<', $today) // Barang yang sudah expired
            ->groupBy('tb_barang.id')
            ->get();

        if ($expiredBarang->isEmpty()) {
            $this->info('Tidak ada barang expired yang perlu dihapus.');
            return 0;
        }

        // Hapus aktivitas dan barang expired
        foreach ($expiredBarang as $barang) {
            \DB::table('tb_aktivitas')->where('id_barang', $barang->id)->delete();
            BarangModel::find($barang->id)->delete();
        }

        $this->info('Barang dan aktivitas expired berhasil dihapus.');
        return 0;
    }
}
