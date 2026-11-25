<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Step 1: Perluas enum dulu (tambah nilai baru, keep yang lama)
            DB::statement("ALTER TABLE student_attendances MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'alpha', 'tidak_hadir') NOT NULL DEFAULT 'hadir'");

            // Step 2: Migrate data lama
            DB::table('student_attendances')->where('status', 'alpha')->update(['status' => 'tidak_hadir']);

            // Step 3: Hapus nilai lama dari enum
            DB::statement("ALTER TABLE student_attendances MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'tidak_hadir') NOT NULL DEFAULT 'hadir'");
        } elseif (DB::getDriverName() === 'sqlite') {
            Schema::disableForeignKeyConstraints();
            Schema::create('student_attendances_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('meeting_id')->constrained('class_meetings')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
                $table->enum('status', ['hadir', 'izin', 'sakit', 'tidak_hadir'])->default('hadir');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
            DB::statement("INSERT INTO student_attendances_temp (id, meeting_id, student_id, status, keterangan, created_at, updated_at) SELECT id, meeting_id, student_id, CASE WHEN status = 'alpha' THEN 'tidak_hadir' ELSE status END, keterangan, created_at, updated_at FROM student_attendances");
            Schema::drop('student_attendances');
            Schema::rename('student_attendances_temp', 'student_attendances');
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->unique(['meeting_id', 'student_id'], 'student_att_meeting_student_unique');
            });
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE student_attendances MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'alpha', 'tidak_hadir') NOT NULL DEFAULT 'hadir'");
            DB::table('student_attendances')->where('status', 'tidak_hadir')->update(['status' => 'alpha']);
            DB::statement("ALTER TABLE student_attendances MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'alpha') NOT NULL DEFAULT 'alpha'");
        }
    }
};
