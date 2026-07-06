<?php

namespace App\Services;

use App\Models\User;
use App\Models\StudentAttendance;
use App\Models\ClassMeeting;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\CourseRepeat;

class StudentAnalyticsService
{
    /**
     * Analyze student academic performance and calculate risk level using custom PHP classification.
     */
    public function analyze(User $student): array
    {
        // 1. Calculate Attendance Rate
        $attendanceRate = 0.0;
        $totalMeetingsCount = 0;
        $attendedMeetingsCount = 0;
        
        if ($student->kelas) {
            $scheduleIds = $student->kelas->schedules->pluck('id');
            $meetings = ClassMeeting::whereIn('schedule_id', $scheduleIds)->get();
            $totalMeetingsCount = $meetings->count();
            
            if ($totalMeetingsCount > 0) {
                $attendedMeetingsCount = StudentAttendance::whereIn('meeting_id', $meetings->pluck('id'))
                    ->where('student_id', $student->id)
                    ->where('status', 'hadir')
                    ->count();
                $attendanceRate = ($attendedMeetingsCount / $totalMeetingsCount) * 100;
            }
        }

        // 2. Calculate Assignment Submission Rate & Avg Score
        $submissionRate = 0.0;
        $averageAssignmentGrade = 0.0;
        $totalAssignmentsCount = 0;
        $submittedCount = 0;
        
        if ($student->kelas) {
            $scheduleIds = $student->kelas->schedules->pluck('id');
            $meetingIds = ClassMeeting::whereIn('schedule_id', $scheduleIds)->pluck('id');
            $assignments = Assignment::whereIn('meeting_id', $meetingIds)->get();
            $totalAssignmentsCount = $assignments->count();
            
            if ($totalAssignmentsCount > 0) {
                $submissions = \App\Models\AssignmentSubmission::whereIn('assignment_id', $assignments->pluck('id'))
                    ->where('student_id', $student->id)
                    ->get();
                $submittedCount = $submissions->count();
                $submissionRate = ($submittedCount / $totalAssignmentsCount) * 100;
                
                $gradedSubmissions = $submissions->whereNotNull('nilai');
                if ($gradedSubmissions->count() > 0) {
                    $averageAssignmentGrade = $gradedSubmissions->avg('nilai');
                }
            }
        }

        // 3. Calculate Overall Grades Average
        $grades = Grade::where('user_id', $student->id)->get();
        $averageExamGrade = 0.0;
        if ($grades->count() > 0) {
            $averageExamGrade = $grades->avg('nilai_akhir');
        }

        // 4. Calculate Repeats History
        $pendingRepeatsCount = CourseRepeat::where('user_id', $student->id)->where('status', 'pending')->count();
        $approvedRepeatsCount = CourseRepeat::where('user_id', $student->id)->where('status', 'approved')->count();

        // 5. Data Science Risk Scoring Model (Decision scoring classifier)
        $riskScore = 0;
        $reasons = [];

        // Feature: Attendance Impact
        if ($attendanceRate < 75.0) {
            $riskScore += 40;
            $reasons[] = "Tingkat kehadiran di bawah 75% ({$attendedMeetingsCount}/{$totalMeetingsCount} pertemuan).";
        } elseif ($attendanceRate < 85.0) {
            $riskScore += 20;
            $reasons[] = "Tingkat kehadiran kurang optimal ({$attendanceRate}%).";
        }

        // Feature: Task Submission Impact
        if ($submissionRate < 50.0) {
            $riskScore += 30;
            $reasons[] = "Mengabaikan lebih dari setengah tugas kuliah ({$submittedCount}/{$totalAssignmentsCount} dikumpulkan).";
        } elseif ($submissionRate < 80.0) {
            $riskScore += 15;
            $reasons[] = "Beberapa tugas kuliah belum dikerjakan ({$submissionRate}% pengumpulan).";
        }

        // Feature: Grade Quality Impact
        $combinedGrade = ($averageExamGrade > 0) ? ($averageExamGrade * 0.7 + $averageAssignmentGrade * 0.3) : $averageAssignmentGrade;
        if ($combinedGrade > 0) {
            if ($combinedGrade < 60) {
                $riskScore += 30;
                $reasons[] = "Rata-rata akumulasi nilai di bawah batas kelulusan ({$combinedGrade}).";
            } elseif ($combinedGrade < 75) {
                $riskScore += 15;
                $reasons[] = "Performa nilai berada di kategori menengah ({$combinedGrade}).";
            }
        }

        // Feature: Repeat Penalty
        if ($pendingRepeatsCount > 0 || $approvedRepeatsCount > 0) {
            $riskScore += 10;
            $reasons[] = "Memiliki riwayat pengulangan mata kuliah aktif.";
        }

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
            ]
        ];
    }
}
