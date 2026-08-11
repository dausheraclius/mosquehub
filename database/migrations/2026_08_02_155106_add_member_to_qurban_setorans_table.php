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
        Schema::table('qurban_setorans', function (Blueprint $table) {
            $table->foreignId('qurban_peserta_member_id')
                ->nullable()
                ->after('qurban_peserta_id')
                ->constrained('qurban_peserta_members')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qurban_setorans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('qurban_peserta_member_id');
        });
    }
};
