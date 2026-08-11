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
        Schema::table('donasis', function (Blueprint $table) {
            $table->enum('kategori', [
                'Zakat',
                'Zakat Fitrah',
                'Zakat Maal',
                'Infaq',
                'Infaq Jumat',
                'Infaq Harian',
                'Sodaqoh',
                'Sodaqoh Dhuafa',
                'Sodaqoh Anak Yatim',
                'Sodaqoh Bencana',
                'Wakaf',
                'Wakaf Uang',
                'Wakaf Tanah',
                'Wakaf Bangunan',
                "Wakaf Al-Qur''an",
                'Donasi',
                'Donasi Bencana',
                'Donasi Pendidikan',
                'Donasi Kesehatan',
                'Donasi Umum',
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donasis', function (Blueprint $table) {
            $table->enum('kategori', [
                'Zakat',
                'Zakat Fitrah',
                'Zakat Maal',
                'Infaq',
                'Infaq Jumat',
                'Infaq Harian',
                'Sodaqoh',
                'Sodaqoh Dhuafa',
                'Sodaqoh Anak Yatim',
                'Sodaqoh Bencana',
                'Wakaf',
                'Donasi',
            ])->change();
        });
    }
};
