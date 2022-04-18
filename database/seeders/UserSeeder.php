<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id'=>1,
                'employee_id'=>1,
                'name'=>'Sameh R.',
                'email'=>'sameh74@msn.com',
                'is_active'=>true,
                'is_admin'=>true,
                'email_verified_at'=>Carbon::now(),
                'password'=>Hash::make('12345'),
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'id'=>2,
                'employee_id'=>2,
                'name'=>'Adham Sameh',
                'email'=>'adham@example.com',
                'is_active'=>true,
                'is_admin'=>true,
                'email_verified_at'=>Carbon::now(),
                'password'=>Hash::make('12345'),
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ]
        ]);
    }
}
