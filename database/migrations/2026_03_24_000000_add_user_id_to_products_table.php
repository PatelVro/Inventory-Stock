<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddUserIdToProductsTable extends Migration
{
    public function up()
    {
        // Add user_id to products table for ownership tracking
        // users.id is int(10) unsigned, so we must match with unsignedInteger
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Add 'technician' to role enum on users table
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','staff','technician') NOT NULL DEFAULT 'staff'");
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','staff') NOT NULL DEFAULT 'staff'");
    }
}
