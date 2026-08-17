<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
    Schema::table('kegiatans', function (Blueprint $table) {
        $table->integer('slot_relawan_max')->nullable()->after('peserta');
    });
    }

    public function down(): void
    {
    Schema::table('kegiatans', function (Blueprint $table) {
        $table->dropColumn('slot_relawan_max');
    });
    }
};