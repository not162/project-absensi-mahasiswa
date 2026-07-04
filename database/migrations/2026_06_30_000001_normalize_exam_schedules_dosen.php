<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropForeign(['pengawas_id']);
            $table->dropColumn('pengawas_id');
        });
    }
};
