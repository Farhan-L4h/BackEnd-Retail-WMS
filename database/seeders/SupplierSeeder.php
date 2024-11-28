<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'nama_supplier' => 'PT.Mayora Indah Tbk',
                'kontak' => '087654321',
                'alamat'=> 'Jl. Tomang Indah'
            ],
            [
                'id' => 2,
                'nama_supplier' => 'PT. Gudang Garam Tbk',
                'kontak'=> '082345678',
                'alamat'=>'Jl. Semampir II'
            ],
            [
                'id' => 3,
                'nama_supplier' => 'PT. Tirta Investama',
                'kontak' => '083579249',
                'alamat'=>'Jl. Raya Surabaya'
            ],
            [
                'id' => 4,
                'nama_supplier' => 'PT. Unilever',
                'kontak' =>'086543258',
                'alamat'=>'Jl. BSD Boulevard Barat'
            ],
            [
                'id' => 5,
                'nama_supplier' => 'PT. Salim Ivomas Pratama Tbk',
                'kontak' => '084335567',
                'alamat' => 'Jl. Tanjung Tembaga'
            ],
        ];

        DB::table('tb_supplier')->insert($data);
    }
}
