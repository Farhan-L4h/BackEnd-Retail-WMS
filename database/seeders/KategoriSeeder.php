<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'nama_kategori' => 'Snack'
            ],
            [
                'id' => 2,
                'nama_kategori' => 'Rokok'
            ],
            [
                'id' => 3,
                'nama_kategori' => 'Minuman'
            ],
            [
                'id' => 4,
                'nama_kategori' => 'Alat Rumah Tangga'
            ],
            [
                'id' => 5,
                'nama_kategori' => 'Sembako'
            ],
        ];

        DB::table('tb_kategori')->insert($data);
    }
}
