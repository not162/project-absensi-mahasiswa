<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->string('pengawas')->nullable()->after('ruangan');
            $table->enum('tipe_soal', ['tulis', 'online', 'take-home'])->default('tulis')->after('pengawas');
            $table->string('tahun_ajaran')->default('2024/2025')->after('tipe_soal');
            $table->enum('periode', ['ganjil', 'genap'])->default('ganjil')->after('tahun_ajaran');
            $table->text('catatan')->nullable()->after('periode');
        });
    }

    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropColumn(['pengawas', 'tipe_soal', 'tahun_ajaran', 'periode', 'catatan']);
        });
    }
};