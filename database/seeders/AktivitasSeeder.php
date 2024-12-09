<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AktivitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'id_barang' => 3,
                'id_user' => 2,
                'id_rak' => 1,
                'exp_barang' => '2025-06-17',
                'jumlah_barang' => 35,
                'harga_barang' => 11000,
                'total_harga' => 385000,
                'status' => 'masuk',
                'alasan' => 'diterima'
            ],
            [
                'id' => 2,
                'id_barang' => 5,
                'id_user' => 2,
                'id_rak' => 1,
                'exp_barang' => '2025-09-15',
                'jumlah_barang' => 50,
                'harga_barang' => 25000,
                'total_harga' => 1250000,
                'status' => 'masuk',
                'alasan' => 'diterima'
            ],
            [
                'id' => 3,
                'id_barang' => 5,
                'id_user' => 2,
                'id_rak' => 1,
                'exp_barang' => '2025-09-15',
                'jumlah_barang' => 20,
                'harga_barang' => 25000,
                'total_harga' => 500000,
                'status' => 'keluar',
                'alasan' => 'diambil'
            ],
        ];

        DB::table('tb_aktivitas')->insert($data);
    }
}
?>
