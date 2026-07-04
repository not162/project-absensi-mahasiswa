<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            // Hapus kolom pengawas lama (string), ganti dengan relasi
            $table->dropColumn('pengawas');
            $table->foreignId('dosen_id')->nullable()->after('ruangan')
                  ->constrained('dosens')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropForeign(['dosen_id']);
            $table->dropColumn('dosen_id');
            $table->string('pengawas')->nullable();
        });
    }
};