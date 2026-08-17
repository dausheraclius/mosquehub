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
    Schema::create('kegiatan_relawan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('kegiatan_id')->constrained()->cascadeOnDelete();
        $table->string('nama');
        $table->string('telepon')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_relawan');
    }
};
