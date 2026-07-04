<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Perluas enum dulu (tambah nilai baru, keep yang lama)
        DB::statement("ALTER TABLE student_attendances MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'alpha', 'tidak_hadir') NOT NULL DEFAULT 'hadir'");

        // Step 2: Migrate data lama
        DB::table('student_attendances')->where('status', 'alpha')->update(['status' => 'tidak_hadir']);

        // Step 3: Hapus nilai lama dari enum
        DB::statement("ALTER TABLE student_attendances MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'tidak_hadir') NOT NULL DEFAULT 'hadir'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE student_attendances MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'alpha', 'tidak_hadir') NOT NULL DEFAULT 'hadir'");
        DB::table('student_attendances')->where('status', 'tidak_hadir')->update(['status' => 'alpha']);
        DB::statement("ALTER TABLE student_attendances MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'alpha') NOT NULL DEFAULT 'alpha'");
    }
};
