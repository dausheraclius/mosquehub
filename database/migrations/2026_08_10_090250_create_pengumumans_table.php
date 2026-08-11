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
    Schema::create('pengumumans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();
        $table->string('judul');
        $table->text('isi');
        $table->string('kategori')->nullable();
        $table->enum('status', ['Aktif', 'Terjadwal', 'Arsip'])->default('Aktif');
        $table->date('tanggal');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumumans');
    }
};
