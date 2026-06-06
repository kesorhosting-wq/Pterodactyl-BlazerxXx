<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrashBinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trash_bin', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('original_path');
            $table->string('file_name');
            $table->string('trash_path');
            $table->boolean('is_file')->default(true);
            $table->timestamp('deleted_at');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trash_bin');
    }
}
