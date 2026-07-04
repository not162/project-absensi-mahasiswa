<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * NOTE: kolom kode_dosen sudah dibuat di migration 2026_06_10_000001.
     * Migration ini dibiarkan kosong (no-op) agar histori migration tetap konsisten
     * tanpa menyebabkan error "duplicate column" saat migrate:fresh.
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'kode_dosen')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('kode_dosen')->unique()->nullable();
            });
        }
    }

    public function down()
    {
        // no-op: kolom dihapus oleh migration 2026_06_10_000001
    }
};
