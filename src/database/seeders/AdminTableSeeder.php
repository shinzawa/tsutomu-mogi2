<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => '管理者1',
            'email' => 'admin1@gmail.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
        ];
        Admin::create($param);
    }
}
