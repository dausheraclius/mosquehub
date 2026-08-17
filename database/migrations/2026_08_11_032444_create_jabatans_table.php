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
    Schema::create('jabatans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mosque_id')->constrained()->cascadeOnDelete();
        $table->string('nama');
        $table->foreignId('parent_id')->nullable()->constrained('jabatans')->nullOnDelete();
        $table->foreignId('jamaah_id')->nullable()->constrained('jamaah')->nullOnDelete();
        $table->integer('urutan')->default(0);
        $table->timestamps();

        $table->unique(['mosque_id', 'nama']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jabatans');
    }
};
