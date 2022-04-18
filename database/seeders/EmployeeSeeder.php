<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('employees')->insert([
            [
                'id'=>1,
                'name'=>'Sameh EL-Hennawy',
                'joining_date'=>Carbon::parse('15-10-2015'),
                'last_working_date'=>null,
                'department_id'=>1,
                'reporting_to'=>null,
                'salary'=>1000,
                'leaves_history'=>null,
                'self_service'=>true,
                'email'=>'sameh74@msn.com',
                'country_code'=>'965',
                'mobile'=>'99150372',
                'job_title'=>'CEO',
                'country_id'=>140,
                'gender'=>1,
                //'is_active'=>true,
            ],
            [
                'id'=>2,
                'name'=>'Adham EL-Hennawy',
                'joining_date'=>Carbon::parse('03-05-2020'),
                'last_working_date'=>null,
                'department_id'=>2,
                'reporting_to'=>1,
                'salary'=>500,
                'leaves_history'=>null,
                'self_service'=>true,
                'email'=>'adham@example.com',
                'country_code'=>'965',
                'mobile'=>'99150373',
                'job_title'=>'HR Manager',
                'country_id'=>140,
                'gender'=>1,
                //'is_active'=>true,
            ]
        ]);
    }
}
