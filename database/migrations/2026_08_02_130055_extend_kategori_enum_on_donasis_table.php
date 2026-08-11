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
                'Sodaqoh',
                'Wakaf',
                'Donasi',
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donasis', function (Blueprint $table) {
            $table->enum('kategori', ['Zakat', 'Infaq', 'Sodaqoh', 'Wakaf', 'Donasi'])->change();
        });
    }
};
