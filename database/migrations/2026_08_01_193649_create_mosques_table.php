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
    Schema::create('mosques', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('short_name')->nullable();
        $table->string('established_year', 4)->nullable();
        $table->string('category')->nullable(); // Masjid Jami' / Masjid Raya / Musholla
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('website')->nullable();
        $table->string('province')->nullable();
        $table->string('city')->nullable();
        $table->string('district')->nullable();
        $table->string('kelurahan')->nullable();
        $table->string('postal_code')->nullable();
        $table->text('address')->nullable();
        $table->string('maps_link')->nullable();
        $table->string('instagram')->nullable();
        $table->string('facebook')->nullable();
        $table->string('youtube')->nullable();
        $table->string('tiktok')->nullable();
        $table->string('whatsapp')->nullable();
        $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mosques');
    }
};
