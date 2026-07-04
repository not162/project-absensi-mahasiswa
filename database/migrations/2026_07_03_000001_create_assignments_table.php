<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel tugas per pertemuan
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('class_meetings')->onDelete('cascade');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->datetime('deadline')->nullable();
            $table->timestamps();
        });

        // Tabel pengumpulan tugas mahasiswa
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->string('link_tugas');
            $table->text('catatan')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['assignment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};