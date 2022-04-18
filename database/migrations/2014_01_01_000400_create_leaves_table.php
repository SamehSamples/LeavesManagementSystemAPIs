<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->Increments('id')->unsigned();
            $table->string('name');
            $table->float('pay_percentage')->default(100);
            $table->tinyInteger('default_block_duration_in_days')->default(0);
            $table->integer('calculation_period')->default(365)->nullable();
            $table->smallInteger('allowed_blocks_per_period')->nullable();
            $table->smallInteger('days_allowed_after')->default(0);
            $table->integer('leave_allowed_after')->unsigned()->nullable();
            $table->boolean('dividable')->default(true);
            $table->boolean('balance_is_accumulated')->default(false);
            $table->boolean('grade_sensitive')->default(false);
            $table->tinyInteger('gender_strict')->nullable();
            $table->boolean('fallback_leave')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('leave_allowed_after')
                ->references('id')->on('leaves');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaves');
    }
};
