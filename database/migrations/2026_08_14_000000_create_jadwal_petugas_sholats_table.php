<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_petugas_sholats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('sholat', ['Jumat', 'Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'])->default('Jumat');
            $table->string('khatib')->nullable();
            $table->string('imam')->nullable();
            $table->string('muadzin')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['mosque_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_petugas_sholats');
    }
};