<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'username' => 'Admin 1',
                'email' => 'admin1@gmail.com',
                'password' => bcrypt('12345'),
                'role' => 'admin'
            ],
            [
                'id' => 2,
                'username' => 'Staff 1',
                'email' => 'staff1@gmail.com',
                'password' => bcrypt('12345'),
                'role' => 'staff'
            ]
            ];
    
            DB::table('users')->insert($data);
    }
}
