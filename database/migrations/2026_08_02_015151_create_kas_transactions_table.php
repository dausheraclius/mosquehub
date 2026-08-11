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
    Schema::create('kas_transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();
        $table->date('tanggal');
        $table->string('jenis');
        $table->string('kategori')->nullable();
        $table->string('keterangan')->nullable();
        $table->decimal('pemasukan', 15, 2)->default(0);
        $table->decimal('pengeluaran', 15, 2)->default(0);
        $table->string('dibuat_oleh')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_transactions');
    }
};
