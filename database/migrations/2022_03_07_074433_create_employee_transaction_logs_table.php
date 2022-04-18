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
        Schema::create('employee_transaction_logs', function (Blueprint $table) {
            $table->Increments('id')->unsigned();
            $table->string('transaction_type');
            $table->integer('by_user_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->json('transaction_details');
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')->on('employees')
                ->onDelete('cascade');

            $table->foreign('by_user_id')
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
        Schema::dropIfExists('employee_transaction_logs');
    }
};
