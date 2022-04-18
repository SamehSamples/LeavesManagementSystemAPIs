<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('leaves')->insert([
            [
                'id' => 1,
                'name' => 'Annual Leave',
                'pay_percentage' => 100,
                'default_block_duration_in_days' => 30,
                'calculation_period' => 365,
                'allowed_blocks_per_period' => 1,
                'days_allowed_after' => 180,
                'leave_allowed_after' => null,
                'dividable'=> true,
                'balance_is_accumulated'=>true,
                'gender_strict'=> null,
                'is_active' => true,
                'fallback_leave' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Sick Leave - Tier 1',
                'pay_percentage' => 100,
                'default_block_duration_in_days' => 15,
                'calculation_period' => 365,
                'allowed_blocks_per_period' => 1,
                'days_allowed_after' => 0,
                'leave_allowed_after' => null,
                'dividable'=> true,
                'balance_is_accumulated'=>false,
                'gender_strict'=> null,
                'is_active' => true,
                'fallback_leave' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'Sick Leave - Tier 2',
                'pay_percentage' => 75,
                'default_block_duration_in_days' => 15,
                'calculation_period' => 365,
                'allowed_blocks_per_period' => 1,
                'days_allowed_after' => 0,
                'leave_allowed_after' => 2,
                'dividable'=> true,
                'balance_is_accumulated'=>false,
                'gender_strict'=> null,
                'is_active' => true,
                'fallback_leave' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'name' => 'Sick Leave - Tier 3',
                'pay_percentage' => 50,
                'default_block_duration_in_days' => 15,
                'calculation_period' => 365,
                'allowed_blocks_per_period' => 1,
                'days_allowed_after' => 0,
                'leave_allowed_after' => 3,
                'dividable'=> true,
                'balance_is_accumulated'=>false,
                'gender_strict'=> null,
                'is_active' => true,
                'fallback_leave' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'name' => 'Sick Leave - Tier 4',
                'pay_percentage' => 0,
                'default_block_duration_in_days' => 15,
                'calculation_period' => 365,
                'allowed_blocks_per_period' => 1,
                'days_allowed_after' => 0,
                'leave_allowed_after' => 4,
                'dividable'=> true,
                'balance_is_accumulated'=>false,
                'gender_strict'=> null,
                'is_active' => true,
                'fallback_leave' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'name' => 'Maternity Leave',
                'pay_percentage' => 100,
                'default_block_duration_in_days' => 90,
                'calculation_period' => null,
                'allowed_blocks_per_period' => 3,
                'days_allowed_after' => 0,
                'leave_allowed_after' => null,
                'dividable'=> false,
                'balance_is_accumulated'=>false,
                'gender_strict'=> 0, // Female Employees Only
                'is_active' => true,
                'fallback_leave' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 7,
                'name' => 'Unpaid Leave',
                'pay_percentage' => 0,
                'default_block_duration_in_days' => 0,
                'calculation_period' => 365,
                'allowed_blocks_per_period' => null,
                'days_allowed_after' => 0,
                'leave_allowed_after' => null,
                'dividable'=> true,
                'balance_is_accumulated'=>false,
                'gender_strict'=> null,
                'is_active' => true,
                'fallback_leave' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 8,
                'name' => 'Condolence Leave',
                'pay_percentage' => 100,
                'default_block_duration_in_days' => 3,
                'calculation_period' => null,
                'allowed_blocks_per_period' => null,
                'days_allowed_after' => 0,
                'leave_allowed_after' => null,
                'dividable'=> false,
                'balance_is_accumulated'=>false,
                'gender_strict'=> null,
                'is_active' => true,
                'fallback_leave' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 9,
                'name' => 'Haj Leave',
                'pay_percentage' => 100,
                'default_block_duration_in_days' => 5,
                'calculation_period' => null,
                'allowed_blocks_per_period' => 1,
                'days_allowed_after' => 0,
                'leave_allowed_after' => null,
                'dividable'=> false,
                'balance_is_accumulated'=>false,
                'gender_strict'=> null,
                'is_active' => true,
                'fallback_leave' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}