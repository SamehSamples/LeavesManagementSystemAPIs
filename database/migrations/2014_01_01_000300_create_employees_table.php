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
        Schema::create('employees', function (Blueprint $table) {
            $table->Increments('id')->unsigned();
            $table->string('name');
            $table->date('joining_date');
            $table->date('last_working_date')->nullable();
            $table->integer('department_id')->unsigned();
            $table->integer('reporting_to')->unsigned()->nullable();
            $table->double('salary');
            $table->json('leaves_history')->nullable();
            $table->boolean('self_service')->default(false);
            $table->string('email')->unique();
            $table->string('country_code','4');
            $table->string('mobile','16');
            $table->string('job_title','100');
            $table->integer('country_id')->unsigned();
            $table->boolean('gender');
            //$table->boolean('is_active')->default(true);
            $table->string('avatar')->nullable();
            $table->timestamps();

            $table->foreign('reporting_to')
                ->references('id')->on('employees')
                ->onDelete('cascade');

            $table->foreign('department_id')
                ->references('id')->on('departments')
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
        Schema::dropIfExists('employees');
    }
};
