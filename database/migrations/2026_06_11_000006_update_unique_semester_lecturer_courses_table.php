<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE: Migrasi ini bisa gagal jika unique index awal terikat FK.
        // Untuk aman, migrasi unique constraint kita tangani di migrasi perbaikan `2026_06_11_000007_fix_unique_semester_lecturer_courses_table`.
    }

    public function down(): void
    {
        // no-op
    }
};



