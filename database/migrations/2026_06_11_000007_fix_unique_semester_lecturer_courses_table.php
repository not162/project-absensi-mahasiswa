<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lecturer_courses', function (Blueprint $table) {
            // Tambahkan kolom semester jika belum ada
            if (!Schema::hasColumn('lecturer_courses', 'semester')) {
                $table->string('semester')->after('course_id');
            }

            // Tambahkan unique baru
            $table->unique(
                ['user_id', 'course_id', 'semester'],
                'lecturer_courses_user_id_course_id_semester_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('lecturer_courses', function (Blueprint $table) {
            $table->dropUnique('lecturer_courses_user_id_course_id_semester_unique');
            $table->dropColumn('semester');
        });
    }
};
