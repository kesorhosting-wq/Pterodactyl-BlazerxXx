<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfileColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_picture_url')) {
                $table->string('profile_picture_url')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'server_order')) {
                $table->text('server_order')->nullable()->after('profile_picture_url');
            }

            if (!Schema::hasColumn('users', 'language')) {
                $table->text('language')->nullable()->after('server_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'server_order')) {
                $table->dropColumn('server_order');
            }

            if (Schema::hasColumn('users', 'profile_picture_url')) {
                $table->dropColumn('profile_picture_url');
            }
            if (Schema::hasColumn('users', 'language')) {
                $table->dropColumn('language');
            }
        });
    }
}