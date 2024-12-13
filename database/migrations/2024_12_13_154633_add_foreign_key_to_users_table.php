<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->references('id')->on('stores')->cascadeOnDelete();
             $table->text('fcm_token')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('store_id');
            $table->dropColumn('fcm_token');
        });
    }
};
