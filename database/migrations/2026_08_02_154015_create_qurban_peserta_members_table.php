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
        Schema::create('qurban_peserta_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qurban_peserta_id')->constrained('qurban_pesertas')->cascadeOnDelete();
            $table->foreignId('jamaah_id')->nullable()->constrained('jamaah')->nullOnDelete();
            $table->string('nama');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qurban_peserta_members');
    }
};
