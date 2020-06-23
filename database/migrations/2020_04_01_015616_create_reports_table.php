<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('compare_hash');
            $table->boolean('isLast')->default(0);
            $table->string('title');
            $table->longText('report');
            $table->longText('summary');
            /**
             * port types are
             * -- REPORT : for report in specific period
             * -- PORT : for each port in specific period
             * -- GENERAL : for total information 
             */
            $table->string('reportType')->default('REPORT');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
