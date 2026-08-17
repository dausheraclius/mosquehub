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
    Schema::create('pengaturan_umums', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();
        $table->string('app_name')->nullable();
        $table->string('timezone')->default('WIB (GMT+7)');
        $table->string('date_format')->default('Masehi');
        $table->string('wa_gateway_number')->nullable();
        $table->boolean('notif_infaq_bulanan')->default(true);
        $table->boolean('notif_agenda_kegiatan')->default(true);
        $table->boolean('notif_jamaah_baru')->default(false);
        $table->boolean('notif_laporan_mingguan')->default(false);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_umums');
    }
};
