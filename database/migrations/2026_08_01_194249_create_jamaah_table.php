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
    Schema::create('jamaah', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();
        $table->string('nama');
        $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
        $table->string('tempat_lahir')->nullable();
        $table->date('tanggal_lahir')->nullable();
        $table->string('no_hp')->nullable();
        $table->string('email')->nullable();
        $table->text('alamat')->nullable();
        $table->string('pekerjaan')->nullable();
        $table->enum('status_pernikahan', ['Belum Menikah', 'Menikah', 'Janda', 'Duda'])->default('Belum Menikah');
        $table->enum('status_jamaah', ['Aktif', 'Tidak Aktif', 'Pindah', 'Wafat'])->default('Aktif');
        $table->date('tanggal_bergabung')->nullable();
        $table->text('catatan')->nullable();
        $table->timestamps();
    });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jamaah');
    }
};
