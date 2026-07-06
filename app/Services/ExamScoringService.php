<?php

namespace App\Services;

class ExamScoringService
{
    /**
     * FUZZY LOGIC MAMDANI
     * Menghitung nilai kelulusan berdasarkan Skor (0-100) dan Waktu (0-120 menit).
     */
    public function evaluateFuzzy($rawScore, $timeTakenMinutes, $departmentName)
    {
        // 1. Terapkan Bobot Prodi (Simple rule: anak IT dapat bonus jika cepat)
        if (stripos($departmentName, 'Informatika') !== false || stripos($departmentName, 'Komputer') !== false) {
            // Jika IT, kecepatan pengerjaan diberi apresiasi
            $timeMultiplier = ($timeTakenMinutes < 60) ? 1.1 : 1.0;
            $rawScore = min(100, $rawScore * $timeMultiplier);
        }

        // 2. Fuzzifikasi
        // SCORE: Rendah (0-50), Sedang (40-75), Tinggi (70-100)
        $scoreTinggi = $this->membershipTriangle($rawScore, 70, 100, 100);
        $scoreSedang = $this->membershipTriangle($rawScore, 40, 60, 75);
        $scoreRendah = $this->membershipTriangle($rawScore, 0, 0, 50);

        // TIME: Cepat (0-60), Normal (50-90), Lama (80-120)
        $timeCepat = $this->membershipTriangle($timeTakenMinutes, 0, 0, 60);
        $timeNormal = $this->membershipTriangle($timeTakenMinutes, 50, 70, 90);
        $timeLama = $this->membershipTriangle($timeTakenMinutes, 80, 120, 120);

        // 3. Inferensi (Rule Base) -> Menghasilkan z (Skor Fuzzy Keputusan 0-100)
        // Rule 1: Jika Score Tinggi & Time Cepat -> Sangat Lulus (100)
        $r1 = min($scoreTinggi, $timeCepat); 
        $z1 = 100;

        // Rule 2: Jika Score Tinggi & Time Lama -> Lulus (80)
        $r2 = min($scoreTinggi, $timeLama);
        $z2 = 80;

        // Rule 3: Jika Score Sedang -> Lulus Bersyarat (60)
        $r3 = $scoreSedang;
        $z3 = 60;

        // Rule 4: Jika Score Rendah -> Remedial (30)
        $r4 = $scoreRendah;
        $z4 = 30;

        // 4. Defuzzifikasi (Metode Sugeno Weighted Average)
        $numerator = ($r1 * $z1) + ($r2 * $z2) + ($r3 * $z3) + ($r4 * $z4);
        $denominator = $r1 + $r2 + $r3 + $r4;

        $fuzzyScore = ($denominator == 0) ? $rawScore : ($numerator / $denominator);
        
        // 5. Keputusan
        $decision = 'Remedial';
        if ($fuzzyScore >= 80) $decision = 'Lulus Murni';
        elseif ($fuzzyScore >= 60) $decision = 'Lulus Bersyarat';

        return [
            'fuzzy_score' => round($fuzzyScore, 2),
            'decision' => $decision
        ];
    }

    private function membershipTriangle($x, $a, $b, $c)
    {
        if ($x <= $a || $x >= $c) return 0;
        if ($x == $b) return 1;
        if ($x > $a && $x < $b) return ($x - $a) / ($b - $a);
        if ($x > $b && $x < $c) return ($c - $x) / ($c - $b);
        return 0;
    }

    /**
     * DIJKSTRA ALGORITHM
     * Mencari jalur remedial terpendek (Graph pathfinding).
     */
    public function getRemedialPath($weakCategory)
    {
        // Graph Materi STEM dan bobot usahanya (waktu belajar dalam jam)
        $graph = [
            'Start' => ['Dasar Logika' => 1, 'Matematika Dasar' => 2],
            'Dasar Logika' => ['Algoritma (Tech)' => 3, 'Matematika Dasar' => 1],
            'Matematika Dasar' => ['Fisika (Science)' => 4, 'Aljabar (Math)' => 2],
            'Aljabar (Math)' => ['Kalkulus (Math)' => 3],
            'Algoritma (Tech)' => ['Pemrograman (Tech)' => 3, 'Sistem Teknik (Eng)' => 4],
            'Fisika (Science)' => ['Mekanika (Science)' => 3, 'Sistem Teknik (Eng)' => 2],
            'Pemrograman (Tech)' => ['Advanced STEM' => 2],
            'Sistem Teknik (Eng)' => ['Advanced STEM' => 3],
            'Kalkulus (Math)' => ['Advanced STEM' => 4],
            'Mekanika (Science)' => ['Advanced STEM' => 2],
            'Advanced STEM' => []
        ];

        // Tentukan Node Awal berdasarkan kategori terlemah mahasiswa
        $startNode = 'Start';
        if ($weakCategory == 'S') $startNode = 'Matematika Dasar';
        if ($weakCategory == 'T') $startNode = 'Dasar Logika';
        if ($weakCategory == 'E') $startNode = 'Fisika (Science)';
        if ($weakCategory == 'M') $startNode = 'Start';

        $targetNode = 'Advanced STEM';

        // Inisialisasi Dijkstra
        $distances = [];
        $previous = [];
        $unvisited = array_keys($graph);

        foreach ($unvisited as $node) {
            $distances[$node] = INF;
            $previous[$node] = null;
        }
        $distances[$startNode] = 0;

        while (count($unvisited) > 0) {
            // Cari node dengan jarak terkecil
            $minNode = null;
            foreach ($unvisited as $node) {
                if ($minNode === null || $distances[$node] < $distances[$minNode]) {
                    $minNode = $node;
                }
            }

            if ($minNode === $targetNode) break; // Sampai di tujuan
            
            // Hapus dari unvisited
            $unvisited = array_diff($unvisited, [$minNode]);

            // Update jarak tetangga
            foreach ($graph[$minNode] as $neighbor => $weight) {
                $alt = $distances[$minNode] + $weight;
                if ($alt < $distances[$neighbor]) {
                    $distances[$neighbor] = $alt;
                    $previous[$neighbor] = $minNode;
                }
            }
        }

        // Traceback path
        $path = [];
        $u = $targetNode;
        while (isset($previous[$u])) {
            array_unshift($path, $u);
            $u = $previous[$u];
        }
        if (count($path) > 0 || $startNode === $targetNode) {
            array_unshift($path, $startNode);
        }

        return [
            'path' => $path,
            'total_effort_hours' => $distances[$targetNode]
        ];
    }
}
