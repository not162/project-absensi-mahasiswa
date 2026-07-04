<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel pertemuan per jadwal (satu baris = satu pertemuan)
        Schema::create('class_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('lecturer_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->integer('pertemuan_ke')->default(1);
            $table->string('materi')->nullable();
            $table->timestamps();
            $table->unique(['schedule_id', 'tanggal'], 'meeting_schedule_date_unique');
        });

        // Tabel absen mahasiswa per pertemuan
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('class_meetings')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('alpha');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['meeting_id', 'student_id'], 'student_att_meeting_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
        Schema::dropIfExists('class_meetings');
    }
};
