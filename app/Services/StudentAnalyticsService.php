<?php

namespace App\Services;

use App\Models\User;
use App\Models\StudentAttendance;
use App\Models\ClassMeeting;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\CourseRepeat;
use App\Models\Schedule;

class StudentAnalyticsService
{
    /**
     * Analyze student academic performance and calculate risk level using custom PHP classification.
     */
    public function analyze(User $student): array
    {
        // 1. Calculate Attendance Rate & Assignment Submission Rate
        $attendanceRate = 0.0;
        $totalMeetingsCount = 0;
        $attendedMeetingsCount = 0;
        $submissionRate = 0.0;
        $averageAssignmentGrade = 0.0;
        $totalAssignmentsCount = 0;
        $submittedCount = 0;
        $gradedSubmissionsCount = 0;

        if ($student->class_id) {
            $scheduleIds = Schedule::where('class_id', $student->class_id)->pluck('id');
            $meetingIds = ClassMeeting::whereIn('schedule_id', $scheduleIds)->pluck('id');
            $totalMeetingsCount = $meetingIds->count();

            if ($totalMeetingsCount > 0) {
                $attendedMeetingsCount = StudentAttendance::whereIn('meeting_id', $meetingIds)
                    ->where('student_id', $student->id)
                    ->where('status', 'hadir')
                    ->count();
                $attendanceRate = ($attendedMeetingsCount / $totalMeetingsCount) * 100;
            }

            $assignmentIds = Assignment::whereIn('meeting_id', $meetingIds)->pluck('id');
            $totalAssignmentsCount = $assignmentIds->count();

            if ($totalAssignmentsCount > 0) {
                $submissions = \App\Models\AssignmentSubmission::whereIn('assignment_id', $assignmentIds)
                    ->where('student_id', $student->id)
                    ->get();
                $submittedCount = $submissions->count();
                $submissionRate = ($submittedCount / $totalAssignmentsCount) * 100;

                $gradedSubmissions = $submissions->whereNotNull('nilai');
                $gradedSubmissionsCount = $gradedSubmissions->count();
                if ($gradedSubmissionsCount > 0) {
                    $averageAssignmentGrade = $gradedSubmissions->avg('nilai');
                }
            }
        }

        // 3. Calculate Overall Grades Average
        $grades = Grade::where('user_id', $student->id)->get();
        $averageExamGrade = 0.0;
        if ($grades->count() > 0) {
            $averageExamGrade = (float) $grades->avg('nilai_akhir');
        }

        // 4. Calculate Repeats History
        $pendingRepeatsCount = CourseRepeat::where('user_id', $student->id)->where('status', 'pending')->count();
        $approvedRepeatsCount = CourseRepeat::where('user_id', $student->id)->where('status', 'disetujui')->count();

        // 5. Data Science Risk Scoring Model (Decision scoring classifier)
        $riskScore = 0;
        $reasons = [];

        $roundedAttendance = round($attendanceRate, 1);
        $roundedSubmission = round($submissionRate, 1);

        // Feature: Attendance Impact (only penalize when meetings exist)
        if ($totalMeetingsCount > 0) {
            if ($attendanceRate < 75.0) {
                $riskScore += 40;
                $reasons[] = "Tingkat kehadiran di bawah 75% ({$attendedMeetingsCount}/{$totalMeetingsCount} pertemuan).";
            } elseif ($attendanceRate < 85.0) {
                $riskScore += 20;
                $reasons[] = "Tingkat kehadiran kurang optimal ({$roundedAttendance}%).";
            }
        }

        // Feature: Task Submission Impact (only penalize when assignments exist)
        if ($totalAssignmentsCount > 0) {
            if ($submissionRate < 50.0) {
                $riskScore += 30;
                $reasons[] = "Mengabaikan lebih dari setengah tugas kuliah ({$submittedCount}/{$totalAssignmentsCount} dikumpulkan).";
            } elseif ($submissionRate < 80.0) {
                $riskScore += 15;
                $reasons[] = "Beberapa tugas kuliah belum dikerjakan ({$roundedSubmission}% pengumpulan).";
            }
        }

        // Feature: Grade Quality Impact
        $hasGrades = ($grades->count() > 0 || $gradedSubmissionsCount > 0);
        $combinedGrade = ($averageExamGrade > 0)
            ? ($gradedSubmissionsCount > 0 ? ($averageExamGrade * 0.7 + $averageAssignmentGrade * 0.3) : $averageExamGrade)
            : $averageAssignmentGrade;
        $roundedCombined = round($combinedGrade, 1);

        if ($hasGrades) {
            if ($combinedGrade < 60) {
                $riskScore += 30;
                $reasons[] = "Rata-rata akumulasi nilai di bawah batas kelulusan ({$roundedCombined}).";
            } elseif ($combinedGrade < 75) {
                $riskScore += 15;
                $reasons[] = "Performa nilai berada di kategori menengah ({$roundedCombined}).";
            }
        }

        // Feature: Repeat Penalty
        if ($pendingRepeatsCount > 0 || $approvedRepeatsCount > 0) {
            $riskScore += 10;
            $reasons[] = "Memiliki riwayat pengulangan mata kuliah aktif.";
        }

        $riskScore = min(100, $riskScore);

        // Output Classification
        if ($riskScore >= 50) {
            $riskLevel = 'AT RISK';
            $riskColor = 'danger';
            $recommendation = "Dibutuhkan intervensi segera! Mahasiswa berisiko tidak lulus semester ini karena tingkat kehadiran rendah dan/atau kualitas nilai yang mengkhawatirkan.";
        } elseif ($riskScore >= 20) {
            $riskLevel = 'GOOD';
            $riskColor = 'warning';
            $recommendation = "Status stabil, namun memerlukan sedikit perbaikan pada tingkat kehadiran atau pengerjaan tugas agar performa akademik optimal.";
        } else {
            $riskLevel = 'EXCELLENT';
            $riskColor = 'success';
            $recommendation = "Status luar biasa! Pertahankan kinerja akademik saat ini untuk meraih hasil terbaik.";
        }

        // STARS Algorithm Rating System
        $starsCount = 5;
        $decisionInput = [];

        // Factor 1: Attendance
        if ($totalMeetingsCount === 0) {
            $decisionInput['attendance'] = 'Belum Ada Pertemuan';
        } elseif ($attendanceRate >= 90.0) {
            $decisionInput['attendance'] = 'Sangat Aktif (>= 90%)';
        } elseif ($attendanceRate >= 75.0) {
            $decisionInput['attendance'] = 'Cukup Aktif (75% - 89%)';
            $starsCount -= 1;
        } else {
            $decisionInput['attendance'] = 'Kritis (< 75%)';
            $starsCount -= 2;
        }

        // Factor 2: Assignment Submission
        if ($totalAssignmentsCount === 0) {
            $decisionInput['submission'] = 'Belum Ada Tugas';
        } elseif ($submissionRate >= 90.0) {
            $decisionInput['submission'] = 'Sangat Patuh (>= 90%)';
        } elseif ($submissionRate >= 70.0) {
            $decisionInput['submission'] = 'Cukup Patuh (70% - 89%)';
            $starsCount -= 1;
        } else {
            $decisionInput['submission'] = 'Kurang Patuh (< 70%)';
            $starsCount -= 2;
        }

        // Factor 3: Grades
        if (!$hasGrades) {
            $decisionInput['grades'] = 'Belum Ada Nilai';
        } elseif ($combinedGrade >= 80.0) {
            $decisionInput['grades'] = 'Sangat Baik (>= 80)';
        } elseif ($combinedGrade >= 60.0) {
            $decisionInput['grades'] = 'Cukup/C (60 - 79)';
            if ($starsCount > 2) $starsCount -= 1;
        } else {
            $decisionInput['grades'] = 'Rendah/D/E (< 60)';
            $starsCount = max(1, $starsCount - 2);
        }

        $starsCount = max(1, $starsCount);

        // Decision Making Response (Output)
        if ($starsCount === 5) {
            $decisionOutput = "Rekomendasi Utama: Pertahankan keaktifan Anda! Anda siap menghadapi ujian teori maupun membuat proyek berskala industri (best-practice) dengan hasil memuaskan.";
        } elseif ($starsCount === 4) {
            $decisionOutput = "Rekomendasi Utama: Tingkatkan kehadiran atau ketepatan pengumpulan tugas Anda. Lakukan review materi teori minimal 2 jam per minggu untuk mengamankan nilai A.";
        } elseif ($starsCount === 3) {
            $decisionOutput = "Rekomendasi Utama: Segera diskusikan dengan dosen pengampu. Rencanakan struktur belajar menggunakan metode Active Recall dan luangkan waktu untuk membaca dokumentasi project standard.";
        } else {
            $decisionOutput = "TINDAKAN DARURAT: Anda terancam tidak lulus semester ini. Buat jadwal bimbingan khusus, perbaiki tingkat absensi, dan ulangi materi fundamental sesegera mungkin.";
        }

        return [
            'metrics' => [
                'attendance_rate' => round($attendanceRate, 1),
                'submission_rate' => round($submissionRate, 1),
                'avg_assignment_grade' => round($averageAssignmentGrade, 1),
                'avg_exam_grade' => round($averageExamGrade, 1),
                'total_meetings' => $totalMeetingsCount,
                'attended_meetings' => $attendedMeetingsCount,
                'total_assignments' => $totalAssignmentsCount,
                'submitted_assignments' => $submittedCount,
                'total_repeats' => $pendingRepeatsCount + $approvedRepeatsCount,
            ],
            'prediction' => [
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'risk_color' => $riskColor,
                'reasons' => $reasons,
                'recommendation' => $recommendation,
            ],
            'stars_analysis' => [
                'stars_count' => $starsCount,
                'decision_input' => $decisionInput,
                'decision_output' => $decisionOutput,
            ]
        ];
    }
}
