<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();
            $table->string('nomor');
            $table->string('subjek');
            $table->enum('jenis', ['Surat Undangan', 'Sertifikat', 'Surat Keterangan', 'Surat Tugas']);
            $table->enum('status', ['Draft', 'Terkirim'])->default('Draft');
            $table->date('tanggal');
            $table->string('kepada')->nullable();
            $table->text('isi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surats');
    }
};