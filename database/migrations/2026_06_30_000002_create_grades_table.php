<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // mahasiswa
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->string('tahun_ajaran')->default('2024/2025');
            $table->decimal('nilai_tugas', 5, 2)->nullable();
            $table->decimal('nilai_uts', 5, 2)->nullable();
            $table->decimal('nilai_uas', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('grade_huruf', 2)->nullable(); // A, AB, B, BC, C, D, E
            $table->timestamps();

            $table->unique(['user_id', 'course_id', 'tahun_ajaran'], 'grades_user_course_ta_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
