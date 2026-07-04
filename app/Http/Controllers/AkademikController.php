<?php
namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Course;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Services\AkademikService;

class AkademikController extends Controller
{
    protected $akademikService;

    public function __construct(AkademikService $akademikService)
    {
        $this->akademikService = $akademikService;
    }

    // Halaman utama - pilih semester
    public function index()
    {
        return view('admin.akademik.index');
    }

    // Halaman per semester - tampilkan kelas & matkul
    public function semester($semester)
    {
        $data = $this->akademikService->getSemesterData($semester);

        return view('admin.akademik.semester', [
            'semester' => $semester,
            'departments' => $data['departments'],
            'kelasList' => $data['kelasList']
        ]);
    }

    // Tambah kelas
    public function createKelas($semester)
    {
        $departments = Department::all();
        return view('admin.akademik.create-kelas', compact('semester', 'departments'));
    }

    public function storeKelas(Request $request, $semester)
    {
        $request->validate([
            'nomor_kelas'   => 'required|integer',
            'department_id' => 'required|exists:departments,id',
            'tahun_ajaran'  => 'required|string',
        ]);

        $this->akademikService->storeKelas($request->all(), $semester);

        return redirect()->route('akademik.semester', $semester)
            ->with('success', 'Kelas berhasil ditambahkan');
    }

    // Assign matkul ke kelas
    public function assignMatkul(Request $request, Kelas $kelas)
    {
        $request->validate([
            'course_ids' => 'required|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        $this->akademikService->assignMatkul($kelas, $request->course_ids);

        return redirect()->back()->with('success', 'Mata kuliah berhasil diassign');
    }

    // Hapus kelas
    public function destroyKelas(Kelas $kelas)
    {
        $this->akademikService->deleteKelas($kelas);
        
        return redirect()->back()->with('success', 'Kelas berhasil dihapus');
    }
}