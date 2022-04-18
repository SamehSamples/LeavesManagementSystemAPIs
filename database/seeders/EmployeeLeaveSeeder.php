<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeLeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('employee_leaves')->insert([
            [
                'id'=>1,
                'employee_id'=>1,
                'leave_id'=>1,
                'duration'=>20,
                'starting_date'=>Carbon::parse('2021-12-05'),
                'ending_date'=>Carbon::parse('2021-12-30'),
                'pay_percentage'=>100,
                'status'=>'approved',
                'actioned_by'=>1,
                'requested_by'=>1,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'id'=>2,
                'employee_id'=>1,
                'leave_id'=>1,
                'duration'=>10,
                'starting_date'=>Carbon::parse('2022-02-06'),
                'ending_date'=>Carbon::parse('2022-02-17'),
                'pay_percentage'=>100,
                'status'=>'approved',
                'actioned_by'=>1,
                'requested_by'=>1,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'id'=>3,
                'employee_id'=>2,
                'leave_id'=>1,
                'duration'=>23,
                'starting_date'=>Carbon::parse('2021-09-05'),
                'ending_date'=>Carbon::parse('2021-10-05'),
                'pay_percentage'=>100,
                'status'=>'approved',
                'actioned_by'=>1,
                'requested_by'=>1,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
        ]);
    }
}
