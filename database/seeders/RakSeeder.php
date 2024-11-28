<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'kode_rak' => 'RK001',
                'nama_rak' => 'Rak 1',
                'lokasi_rak' => 'Kiri'
            ],
            [
                'id' => 2,
                'kode_rak' => 'RK002',
                'nama_rak' => 'Rak 2',
                'lokasi_rak' => 'Kanan'
            ],
            [
                'id' => 3,
                'kode_rak' => 'RK003',
                'nama_rak' => 'Rak 3',
                'lokasi_rak' => 'Kiri'
            ],
            [
                'id' => 4,
                'kode_rak' => 'RK004',
                'nama_rak' => 'Rak 4',
                'lokasi_rak' => 'Kanan'
            ],
            [
                'id' => 5,
                'kode_rak' => 'RD001',
                'nama_rak' => 'Rak Display 1',
                'lokasi_rak' => 'Toko'
            ],
        ];

        DB::table('tb_rak')->insert($data);
    }
}
?>
