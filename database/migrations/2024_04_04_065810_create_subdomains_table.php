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
        Schema::create('subdomains', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->foreign('server_id')->references('id')->on('servers');
            $table->unsignedInteger('allocation_id');
            $table->foreign('allocation_id')->references('id')->on('allocations');
            $table->unsignedBigInteger('domain_id');
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
            $table->string('subdomain');
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
        Schema::dropIfExists('subdomains');
    }
};
