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
        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->Increments('id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->integer('leave_id')->unsigned();
            $table->integer('duration');
            $table->date('starting_date');
            $table->date('ending_date');
            $table->double('pay_percentage');
            $table->string('status'); // values: self_approved, requested, approved, rejected, withdrawn, consumed
            $table->integer('requested_by')->unsigned();
            $table->integer('actioned_by')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')->on('employees')
                ->onDelete('cascade');

            $table->foreign('leave_id')
                ->references('id')->on('leaves')
                ->onDelete('cascade');

            $table->foreign('actioned_by')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('requested_by')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_leaves');
    }
};
