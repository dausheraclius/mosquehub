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
        Schema::table('pengaturan_umums', function (Blueprint $table) {
            $table->time('sholat_subuh')->nullable();
            $table->time('sholat_dzuhur')->nullable();
            $table->time('sholat_ashar')->nullable();
            $table->time('sholat_maghrib')->nullable();
            $table->time('sholat_isya')->nullable();
            $table->boolean('tampil_sholat_subuh')->default(true);
            $table->boolean('tampil_sholat_dzuhur')->default(true);
            $table->boolean('tampil_sholat_ashar')->default(true);
            $table->boolean('tampil_sholat_maghrib')->default(true);
            $table->boolean('tampil_sholat_isya')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_umums', function (Blueprint $table) {
            $table->dropColumn([
                'sholat_subuh',
                'sholat_dzuhur',
                'sholat_ashar',
                'sholat_maghrib',
                'sholat_isya',
                'tampil_sholat_subuh',
                'tampil_sholat_dzuhur',
                'tampil_sholat_ashar',
                'tampil_sholat_maghrib',
                'tampil_sholat_isya',
            ]);
        });
    }
};
