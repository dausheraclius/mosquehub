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
    Schema::create('qurban_setorans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('qurban_peserta_id')->constrained()->cascadeOnDelete();
        $table->date('tanggal');
        $table->decimal('jumlah', 15, 2);
        $table->string('metode')->nullable();
        $table->string('petugas')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qurban_setorans');
    }
};
