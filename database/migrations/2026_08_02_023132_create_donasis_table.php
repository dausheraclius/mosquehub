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
    Schema::create('donasis', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();
        $table->date('tanggal');
        $table->string('donatur');
        $table->enum('kategori', ['Zakat', 'Infaq', 'Sodaqoh', 'Wakaf', 'Donasi']);
        $table->string('jenis'); // cth: "Zakat Fitrah", "Infaq Jumat", dst
        $table->enum('tipe', ['Uang', 'Barang'])->default('Uang');
        $table->decimal('nominal', 15, 2);
        $table->text('keterangan')->nullable();
        $table->string('metode')->nullable();
        $table->string('petugas')->nullable();
        $table->enum('status', ['Berhasil', 'Pending'])->default('Berhasil');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donasis');
    }
};
