<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::disableForeignKeyConstraints();
            Schema::create('exam_schedules_temp', function (Blueprint $table) {
                $table->id();
                $table->enum('tipe', ['uts', 'uas']);
                $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->integer('semester');
                $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
                $table->date('tanggal');
                $table->time('jam_mulai');
                $table->time('jam_selesai');
                $table->string('ruangan');
                $table->foreignId('pengawas_id')->nullable()->constrained('users')->onDelete('set null');
                $table->enum('tipe_soal', ['tulis', 'online', 'take-home'])->default('tulis');
                $table->string('tahun_ajaran')->default('2024/2025');
                $table->enum('periode', ['ganjil', 'genap'])->default('ganjil');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
            DB::statement('INSERT INTO exam_schedules_temp (id, tipe, course_id, class_id, semester, department_id, tanggal, jam_mulai, jam_selesai, ruangan, tipe_soal, tahun_ajaran, periode, catatan, created_at, updated_at) SELECT id, tipe, course_id, class_id, semester, department_id, tanggal, jam_mulai, jam_selesai, ruangan, tipe_soal, tahun_ajaran, periode, catatan, created_at, updated_at FROM exam_schedules');
            Schema::drop('exam_schedules');
            Schema::rename('exam_schedules_temp', 'exam_schedules');
            Schema::enableForeignKeyConstraints();
            return;
        }

        Schema::table('exam_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('exam_schedules', 'dosen_id')) {
                $table->dropForeign(['dosen_id']);
                $table->dropColumn('dosen_id');
            }
            if (!Schema::hasColumn('exam_schedules', 'pengawas_id')) {
                $table->foreignId('pengawas_id')->nullable()->after('ruangan')
                      ->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropForeign(['pengawas_id']);
            $table->dropColumn('pengawas_id');
        });
    }
};
