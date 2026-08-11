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
    Schema::table('users', function (Blueprint $table) {
        $table->foreignId('mosque_id')->nullable()->after('id')->constrained()->nullOnDelete();
        $table->string('phone')->nullable()->after('email');
        $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('phone');
        $table->timestamp('last_login_at')->nullable()->after('status');
    });
}

    public function down(): void
    {
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['mosque_id']);
        $table->dropColumn(['mosque_id', 'phone', 'status', 'last_login_at']);
    });
    }
};
