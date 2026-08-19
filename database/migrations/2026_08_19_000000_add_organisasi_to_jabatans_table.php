<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DDL MySQL tidak atomik. Cek kolom ini membuat migrasi tetap aman
        // dijalankan ulang bila proses sebelumnya berhenti setelah ADD COLUMN.
        if (! Schema::hasColumn('jabatans', 'organisasi')) {
            Schema::table('jabatans', function (Blueprint $table) {
                $table->string('organisasi', 50)->default('YMBPK')->after('mosque_id');
            });
        }

        // Indeks unik lama juga menjadi indeks pendukung foreign key mosque_id.
        // Buat indeks pengganti sebelum menghapusnya.
        if (! $this->hasIndex('jabatans_mosque_id_index')) {
            Schema::table('jabatans', fn (Blueprint $table) => $table->index('mosque_id'));
        }

        // Proses ini dibuat idempoten karena ALTER TABLE MySQL tidak atomik.
        if ($this->hasForeignKey('jabatans_parent_id_foreign')) {
            Schema::table('jabatans', fn (Blueprint $table) => $table->dropForeign(['parent_id']));
        }

        if ($this->hasIndex('jabatans_mosque_id_nama_unique')) {
            Schema::table('jabatans', fn (Blueprint $table) => $table->dropUnique(['mosque_id', 'nama']));
        }

        if (! $this->hasIndex('jabatans_mosque_id_organisasi_nama_unique')) {
            Schema::table('jabatans', fn (Blueprint $table) => $table->unique(['mosque_id', 'organisasi', 'nama']));
        }

        if (! $this->hasForeignKey('jabatans_parent_id_foreign')) {
            Schema::table('jabatans', fn (Blueprint $table) => $table->foreign('parent_id')->references('id')->on('jabatans')->nullOnDelete());
        }

        // Nilai default YMBPK pada kolom baru mempertahankan seluruh struktur
        // lama. IKRAM sengaja tidak dibuat di sini karena sifatnya opsional.
    }

    public function down(): void
    {
        if ($this->hasForeignKey('jabatans_parent_id_foreign')) {
            Schema::table('jabatans', fn (Blueprint $table) => $table->dropForeign(['parent_id']));
        }

        if ($this->hasIndex('jabatans_mosque_id_organisasi_nama_unique')) {
            Schema::table('jabatans', fn (Blueprint $table) => $table->dropUnique(['mosque_id', 'organisasi', 'nama']));
        }

        if (! $this->hasIndex('jabatans_mosque_id_nama_unique')) {
            Schema::table('jabatans', fn (Blueprint $table) => $table->unique(['mosque_id', 'nama']));
        }

        if ($this->hasIndex('jabatans_mosque_id_index')) {
            Schema::table('jabatans', fn (Blueprint $table) => $table->dropIndex(['mosque_id']));
        }

        Schema::table('jabatans', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('jabatans')->nullOnDelete();
            $table->dropColumn('organisasi');
        });
    }

    private function hasIndex(string $name): bool
    {
        return collect(Schema::getIndexes('jabatans'))->contains(fn (array $index) => $index['name'] === $name);
    }

    private function hasForeignKey(string $name): bool
    {
        return collect(Schema::getForeignKeys('jabatans'))->contains(fn (array $foreignKey) => $foreignKey['name'] === $name);
    }
};
