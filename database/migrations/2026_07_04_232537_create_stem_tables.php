<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stem_exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('duration_minutes')->default(120);
            $table->timestamps();
        });

        Schema::create('stem_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('stem_exams')->onDelete('cascade');
            $table->enum('category', ['S', 'T', 'E', 'M'])->comment('Science, Tech, Eng, Math');
            $table->text('question_text');
            $table->string('opt_a');
            $table->string('opt_b');
            $table->string('opt_c');
            $table->string('opt_d');
            $table->char('correct_opt', 1);
            $table->timestamps();
        });

        Schema::create('stem_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained('stem_exams')->onDelete('cascade');
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            
            // Raw score (0-100)
            $table->float('raw_score')->nullable();
            
            // Fuzzy logic output
            $table->float('fuzzy_score')->nullable();
            $table->string('decision')->nullable(); // e.g. Lulus, Remedial
            
            // A* / Dijkstra remedial path recommended
            $table->json('remedial_path')->nullable();
            
            $table->timestamps();
        });

        Schema::create('stem_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('stem_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('stem_questions')->onDelete('cascade');
            $table->char('selected_opt', 1)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stem_answers');
        Schema::dropIfExists('stem_attempts');
        Schema::dropIfExists('stem_questions');
        Schema::dropIfExists('stem_exams');
    }
};
