<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // File soal tugas dari dosen (bisa diunduh mahasiswa)
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('file_tugas')->nullable()->after('deskripsi');
            $table->string('file_tugas_original')->nullable()->after('file_tugas');
        });

        // File jawaban tugas dari mahasiswa
        // Catatan: kolom link_tugas dibiarkan seperti semula (NOT NULL) supaya
        // tidak butuh package doctrine/dbal untuk ->change(). Saat menyimpan
        // tanpa link, controller akan mengisi string kosong ''.
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->string('file_tugas')->nullable()->after('assignment_id');
            $table->string('file_tugas_original')->nullable()->after('file_tugas');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['file_tugas', 'file_tugas_original']);
        });

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn(['file_tugas', 'file_tugas_original']);
        });
    }
};
