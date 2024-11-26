<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id_barang' => 1,
                'image' => 'images/default.png',
                'id_kategori' => 1,
                'id_supplier' => 1,
                'nama_barang' => 'Beng-beng',
                'deskripsi' => 'Makanan Enak',
                'harga' => '3000',
            ],
            [
                'id_barang' => 2,
                'image' => 'images/default.png',
                'id_kategori' => 2,
                'id_supplier' => 2,
                'nama_barang' => 'Surya',
                'deskripsi' => 'Rokok membunuhmu',
                'harga' => '26000',
            ],
            [
                'id_barang' => 3,
                'image' => 'images/default.png',
                'id_kategori' => 3,
                'id_supplier' => 3,
                'nama_barang' => 'Aqua',
                'deskripsi' => 'Air Mineral Berkualitas',
                'harga' => '11000',
            ],
            [
                'id_barang' => 4,
                'image' => 'images/default.png',
                'id_kategori' => 4,
                'id_supplier' => 4,
                'nama_barang' => 'Lifeboy',
                'deskripsi' => 'Sabun ramah',
                'harga' => '25000',
            ],
            [
               'id_barang' => 5,
                'image' => 'images/default.png',
                'id_kategori' => 5,
                'id_supplier' => 5,
                'nama_barang' => 'Minyak Bimoli',
                'deskripsi' => 'Goreng, Masak, Tumis Bimoli',
                'harga' => '25000',
            ],
        ];

        DB::table('tb_barang')->insert($data);
    }
}
?>
