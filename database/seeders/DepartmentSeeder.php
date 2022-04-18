<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('departments')->insert([
            [
                'id'=>1,
                'name'=>'General Management',
                //'manager_id'=>1,
                'is_active'=>true,
            ],
            [
                'id'=>2,
                'name'=>'Human Resources',
                //'manager_id'=>2,
                'is_active'=>true,
            ]
        ]);
    }
}
