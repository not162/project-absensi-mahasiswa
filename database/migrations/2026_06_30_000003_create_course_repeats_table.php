<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_repeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // mahasiswa pengajuan
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // matkul yg diulang
            $table->foreignId('grade_id')->nullable()->constrained('grades')->onDelete('set null'); // nilai lama (opsional)
            $table->string('tahun_ajaran_target')->nullable(); // tahun ajaran ingin mengulang
            $table->text('alasan')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_repeats');
    }
};
