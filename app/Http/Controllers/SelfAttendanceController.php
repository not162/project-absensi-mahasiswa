<?php
namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ClassMeeting;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SelfAttendanceController extends Controller
{
    /** Mahasiswa: halaman jadwal + self check-in + tugas */
    public function index()
    {
        $user  = Auth::user();
        $kelas = $user->kelas;
        $today = Carbon::today();
        $hariMap = [
            'Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
            'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu',
        ];
        $hariIni = $hariMap[$today->format('l')] ?? $today->format('l');

        $schedules = $kelas
            ? Schedule::with(['course', 'lecturer'])
                ->where('class_id', $kelas->id)
                ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
                ->orderBy('jam_mulai')
                ->get()
            : collect();

        // Cek absen hari ini & seluruh tugas (dari semua pertemuan) per jadwal
        $attendanceStatus  = [];
        $meetingData       = [];
        $assignmentsByJadwal = [];

        foreach ($schedules as $schedule) {
            $isHariIni = ($hariIni === $schedule->hari);
            
            if ($isHariIni) {
                $meeting = ClassMeeting::firstOrCreate(
                    ['schedule_id' => $schedule->id, 'tanggal' => $today],
                    [
                        'lecturer_id'   => $schedule->user_id,
                        'pertemuan_ke'  => ClassMeeting::where('schedule_id', $schedule->id)->count() + 1,
                        'materi'        => '',
                    ]
                );
            } else {
                $meeting = ClassMeeting::where('schedule_id', $schedule->id)
                    ->whereDate('tanggal', $today)
                    ->first();
            }

            $meetingData[$schedule->id] = $meeting;

            if ($meeting) {
                $absen = StudentAttendance::where('meeting_id', $meeting->id)
                    ->where('student_id', $user->id)
                    ->first();
                $attendanceStatus[$schedule->id] = $absen?->status;
            }

            // Semua tugas dari semua pertemuan jadwal ini (bukan cuma hari ini)
            $meetingIdsSchedule = ClassMeeting::where('schedule_id', $schedule->id)->pluck('id');
            $assignmentsByJadwal[$schedule->id] = \App\Models\Assignment::whereIn('meeting_id', $meetingIdsSchedule)
                ->with(['submissions' => function ($q) use ($user) {
                    $q->where('student_id', $user->id);
                }, 'meeting'])
                ->orderByDesc('created_at')
                ->get();
        }

        return view('mahasiswa.jadwal', compact(
            'schedules', 'hariIni', 'today',
            'attendanceStatus', 'meetingData', 'assignmentsByJadwal', 'user'
        ));
    }

    /** Mahasiswa: self check-in (klik Hadir sendiri) */
    public function checkIn(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'status'      => 'required|in:hadir,izin,sakit,tidak_hadir',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
        ]);

        $user     = Auth::user();
        $schedule = Schedule::findOrFail($request->schedule_id);
        $today    = Carbon::today();

        // Pastikan mahasiswa ada di kelas jadwal ini
        abort_if($schedule->class_id !== $user->class_id, 403);

        // Cari atau buat pertemuan hari ini secara otomatis
        $meeting = ClassMeeting::firstOrCreate(
            ['schedule_id' => $schedule->id, 'tanggal' => $today],
            [
                'lecturer_id'   => $schedule->user_id,
                'pertemuan_ke'  => ClassMeeting::where('schedule_id', $schedule->id)->count() + 1,
                'materi'        => '',
            ]
        );

        // Validasi Geolocation dinonaktifkan atas permintaan user agar mahasiswa bisa absen dari mana saja tanpa kendala jarak.
        if ($request->status === 'hadir') {
            if ($request->latitude && $request->longitude) {
                // Simpan lokasi jika tersedia (opsional)
            }
        }

        StudentAttendance::updateOrCreate(
            ['meeting_id' => $meeting->id, 'student_id' => $user->id],
            ['status' => $request->status]
        );

        return redirect()->back()->with('success', 'Kehadiran berhasil dicatat.');
    }

    /** Helper: Hitung jarak Geolocation (Haversine Formula) dalam meter */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }


    /** Mahasiswa: Halaman simulasi TOEFL / IELTS */
    public function toeflExam()
    {
        $results = \App\Models\ToeflResult::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        return view('mahasiswa.toefl', compact('results'));
    }

    public function takeToefl()
    {
        $questions = $this->getMockToeflQuestions();
        return view('mahasiswa.toefl_take', compact('questions'));
    }

    public function submitToefl(Request $request)
    {
        $answers = $request->input('answers', []);
        $questions = $this->getMockToeflQuestions();

        $listeningCorrect = 0;
        $structureCorrect = 0;
        $readingCorrect = 0;

        foreach ($questions as $q) {
            $selectedOpt = $answers[$q['id']] ?? null;
            if ($selectedOpt === $q['correct']) {
                if ($q['section'] === 'listening') {
                    $listeningCorrect++;
                } elseif ($q['section'] === 'structure') {
                    $structureCorrect++;
                } elseif ($q['section'] === 'reading') {
                    $readingCorrect++;
                }
            }
        }

        // Scale scores (Normally 31-68 for each section, total formula: ((S1 + S2 + S3) * 10) / 3)
        $listeningScore = 310 + ($listeningCorrect / 3) * 367;
        $structureScore = 310 + ($structureCorrect / 3) * 367;
        $readingScore = 310 + ($readingCorrect / 4) * 367;
        
        $totalScore = round(($listeningScore + $structureScore + $readingScore) / 3);

        $result = \App\Models\ToeflResult::create([
            'user_id' => Auth::id(),
            'listening_score' => round($listeningScore),
            'structure_score' => round($structureScore),
            'reading_score' => round($readingScore),
            'total_score' => $totalScore,
        ]);

        return redirect()->route('mahasiswa.toefl.result', $result->id)->with('success', 'Simulasi TOEFL berhasil diselesaikan.');
    }

    public function toeflResult(int $id)
    {
        $result = \App\Models\ToeflResult::findOrFail($id);
        $questions = $this->getMockToeflQuestions();
        return view('mahasiswa.toefl_result', compact('result', 'questions'));
    }

    private function getMockToeflQuestions()
    {
        return [
            [
                'id' => 1,
                'section' => 'listening',
                'question' => '(Man) I\'m going to buy a new car. (Woman) Are you sure you can afford it? (Narrator) What does the woman imply?',
                'opt_a' => 'She thinks he should buy a bigger car.',
                'opt_b' => 'She doubts he has enough money for a car.',
                'opt_c' => 'She wants to borrow his new car.',
                'opt_d' => 'She is excited about his purchase.',
                'correct' => 'B'
            ],
            [
                'id' => 2,
                'section' => 'listening',
                'question' => '(Man) The weather is terrible today. (Woman) I know, I wish we had stayed home. (Narrator) What does the woman mean?',
                'opt_a' => 'She wanted to stay home.',
                'opt_b' => 'She enjoys terrible weather.',
                'opt_c' => 'She wants to go outside.',
                'opt_d' => 'She thinks the weather will improve.',
                'correct' => 'A'
            ],
            [
                'id' => 3,
                'section' => 'listening',
                'question' => '(Man) Did you finish the math assignment? (Woman) It was a piece of cake. (Narrator) What does the woman mean?',
                'opt_a' => 'The assignment was tasty.',
                'opt_b' => 'She ate cake while doing math.',
                'opt_c' => 'The assignment was very easy.',
                'opt_d' => 'She has not started it yet.',
                'correct' => 'C'
            ],
            [
                'id' => 4,
                'section' => 'structure',
                'question' => 'The North Pole _______ a latitude of 90 degrees north.',
                'opt_a' => 'it has',
                'opt_b' => 'having',
                'opt_c' => 'has',
                'opt_d' => 'which has',
                'correct' => 'C'
            ],
            [
                'id' => 5,
                'section' => 'structure',
                'question' => '______ of the finance committee, she will deliver the annual budget report.',
                'opt_a' => 'As chairperson',
                'opt_b' => 'She is chairperson',
                'opt_c' => 'The chairperson',
                'opt_d' => 'To be chairperson',
                'correct' => 'A'
            ],
            [
                'id' => 6,
                'section' => 'structure',
                'question' => 'Rarely _______ for more than a few days in the desert.',
                'opt_a' => 'rain falls',
                'opt_b' => 'it rains',
                'opt_c' => 'does it rain',
                'opt_d' => 'rains it',
                'correct' => 'C'
            ],
            [
                'id' => 7,
                'section' => 'reading',
                'question' => 'The ozone layer is a region of Earth\'s stratosphere that absorbs most of the Sun\'s ultraviolet radiation. It contains high concentrations of ozone (O3). According to the passage, what does the ozone layer absorb?',
                'opt_a' => 'Infrared radiation',
                'opt_b' => 'Ultraviolet radiation',
                'opt_c' => 'High ozone concentrations',
                'opt_d' => 'Visible light',
                'correct' => 'B'
            ],
            [
                'id' => 8,
                'section' => 'reading',
                'question' => 'Photosynthesis is a process used by plants to convert light energy into chemical energy that can later be released to fuel the organisms\' activities. Chemical energy is stored in carbohydrate molecules, such as sugars. In what form is chemical energy stored?',
                'opt_a' => 'Light energy',
                'opt_b' => 'Carbohydrate molecules like sugars',
                'opt_c' => 'Oxygen gas',
                'opt_d' => 'Soil minerals',
                'correct' => 'B'
            ],
            [
                'id' => 9,
                'section' => 'reading',
                'question' => 'The Great Barrier Reef is the world\'s largest coral reef system, composed of over 2,900 individual reefs. It is located in the Coral Sea, off the coast of Queensland, Australia. Where is the Great Barrier Reef located?',
                'opt_a' => 'In the Tasman Sea',
                'opt_b' => 'In the Indian Ocean',
                'opt_c' => 'In the Coral Sea, off Queensland',
                'opt_d' => 'Off the coast of Western Australia',
                'correct' => 'C'
            ],
            [
                'id' => 10,
                'section' => 'reading',
                'question' => 'The word "convert" in the passage about photosynthesis is closest in meaning to:',
                'opt_a' => 'destroy',
                'opt_b' => 'transform',
                'opt_c' => 'release',
                'opt_d' => 'absorb',
                'correct' => 'B'
            ]
        ];
    }

    /** Mahasiswa: Jadwal kelas per program studi */
    public function jadwalKelas(Request $request)
    {
        $user = Auth::user();
        $departments = \App\Models\Department::all();
        
        // Pilih prodi yang akan ditampilkan, default prodi mahasiswa itu sendiri
        $selectedDeptId = $request->get('department_id', $user->department_id ?? $departments->first()?->id);
        
        $schedules = Schedule::with(['course.department', 'lecturer', 'kelas.department'])
            ->whereHas('course', function($query) use ($selectedDeptId) {
                $query->where('department_id', $selectedDeptId);
            })
            ->get();

        return view('mahasiswa.jadwal_kelas', compact('schedules', 'departments', 'selectedDeptId', 'user'));
    }

    /** Simpan feedback pengajaran dosen */
    public function storeFeedback(Request $request)
    {
        $request->validate([
            'schedule_id'     => 'required|exists:schedules,id',
            'feedback_dosen'  => 'required|string|max:500',
            'feedback_sesuai' => 'required|in:Sesuai,Tidak Sesuai',
        ]);

        $user  = Auth::user();
        $today = Carbon::today();

        $meeting = ClassMeeting::where('schedule_id', $request->schedule_id)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$meeting) {
            return redirect()->back()->with('error', 'Pertemuan tidak ditemukan.');
        }

        StudentAttendance::updateOrCreate(
            ['meeting_id' => $meeting->id, 'student_id' => $user->id],
            [
                'feedback_dosen'  => $request->feedback_dosen,
                'feedback_sesuai' => $request->feedback_sesuai,
            ]
        );

        return redirect()->back()->with('success', 'Umpan balik pengajaran berhasil dikirim.');
    }

    /** Rekap Absen Mahasiswa */
    public function rekapAbsen()
    {
        $user = Auth::user();
        
        $attendances = StudentAttendance::with(['meeting.schedule.course', 'meeting.schedule.lecturer'])
            ->where('student_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mahasiswa.rekap_absen', compact('attendances', 'user'));
    }
}