<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exam = \App\Models\StemExam::create([
            'title' => 'Ujian Evaluasi Kemampuan STEM',
            'duration_minutes' => 120
        ]);

        $questions = [
            // Science (S)
            ['S', '[MIT OCW] Apa yang membedakan sel hewan dengan sel tumbuhan?', 'Sel tumbuhan memiliki dinding sel', 'Sel hewan memiliki kloroplas', 'Sel tumbuhan tidak memiliki inti', 'Keduanya identik', 'A'],
            ['S', '[Harvard] Proses apa yang digunakan tumbuhan untuk mengubah sinar matahari menjadi makanan?', 'Respirasi seluler', 'Fotosintesis', 'Transpirasi', 'Fermentasi', 'B'],
            // Technology (T)
            ['T', '[Stanford] Protokol apa yang digunakan untuk transfer halaman web di internet?', 'FTP', 'SMTP', 'HTTP', 'SSH', 'C'],
            ['T', '[MIT OCW] Dalam arsitektur komputer, apa fungsi dari RAM?', 'Penyimpanan permanen', 'Memori jangka pendek', 'Pemrosesan grafis', 'Pendinginan sistem', 'B'],
            // Engineering (E)
            ['E', '[Stanford] Gaya apa yang bekerja berlawanan arah dengan gerak benda pada permukaan?', 'Gravitasi', 'Normal', 'Gesek', 'Sentripetal', 'C'],
            ['E', '[Harvard] Material manakah yang merupakan konduktor listrik terbaik?', 'Plastik', 'Kayu', 'Tembaga', 'Kaca', 'C'],
            // Math (M)
            ['M', '[Oxford Math] Berapa nilai x jika 2x + 5 = 15?', '5', '10', '7.5', '2.5', 'A'],
            ['M', '[Oxford Math] Berapa turunan dari f(x) = x^2?', 'x', '2x', '2', 'x^3', 'B'],
        ];

        foreach ($questions as $q) {
            \App\Models\StemQuestion::create([
                'exam_id' => $exam->id,
                'category' => $q[0],
                'question_text' => $q[1],
                'opt_a' => $q[2],
                'opt_b' => $q[3],
                'opt_c' => $q[4],
                'opt_d' => $q[5],
                'correct_opt' => $q[6],
            ]);
        }
    }
}
