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
    Schema::create('qurban_pesertas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();
        $table->string('nama');
        $table->string('paket');
        $table->decimal('target', 15, 2);
        $table->date('mulai');
        $table->timestamps();
    });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qurban_pesertas');
    }
};
