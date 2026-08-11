<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->time('jam_mulai')->nullable()->change();
            $table->time('jam_selesai')->nullable()->change();
            $table->string('lokasi')->nullable()->change();
            $table->string('pemateri')->nullable()->change();
            $table->string('pj')->nullable()->change();
            $table->integer('peserta')->nullable()->change();
            $table->text('deskripsi')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->time('jam_mulai')->nullable(false)->change();
            $table->time('jam_selesai')->nullable(false)->change();
            $table->string('lokasi')->nullable(false)->change();
            $table->string('pemateri')->nullable(false)->change();
            $table->string('pj')->nullable(false)->change();
            $table->integer('peserta')->nullable(false)->change();
            $table->text('deskripsi')->nullable(false)->change();
        });
    }
};