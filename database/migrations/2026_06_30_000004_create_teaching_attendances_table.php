<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // dosen
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'tidak_hadir'])->default('hadir');
            $table->string('materi')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'tanggal'], 'teaching_att_schedule_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_attendances');
    }
};
