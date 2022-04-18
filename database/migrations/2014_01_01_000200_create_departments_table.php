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
        Schema::create('departments', function (Blueprint $table) {
            $table->Increments('id')->unsigned();
            $table->string('name');
            $table->integer('manager_id')->unsigned()->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            /*$table->foreign('manager_id')
                ->references('id')->on('employees')
                ->onDelete('cascade');*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('departments');
    }
};
