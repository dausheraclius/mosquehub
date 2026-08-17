<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventaris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->string('kategori')->nullable();
            $table->string('lokasi')->nullable();
            $table->enum('kondisi', ['Baik', 'Perlu Perbaikan', 'Rusak', 'Nonaktif'])->default('Baik');
            $table->string('qty')->default('1');
            $table->enum('sumber', ['Beli', 'Waqaf'])->default('Beli');
            $table->string('kode')->unique();
            $table->date('tgl_beli')->nullable();
            $table->string('harga')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaris');
    }
};