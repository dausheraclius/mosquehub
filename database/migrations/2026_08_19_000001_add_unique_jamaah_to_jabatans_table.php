<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // NULL tetap diperbolehkan berulang untuk jabatan yang belum terisi.
            $table->unique(['mosque_id', 'jamaah_id'], 'jabatans_mosque_jamaah_unique');
        });
    }

    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropUnique('jabatans_mosque_jamaah_unique');
        });
    }
};
