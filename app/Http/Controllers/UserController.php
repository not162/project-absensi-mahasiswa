<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $userService;

    public function __construct(\App\Services\UserService $userService)
    {
        $this->middleware('auth');
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // If request is AJAX, route it to our Service (API Gateway pattern) for SSR Datatables
        if ($request->ajax()) {
            return $this->userService->getUsersDataTable($request);
        }

        $departments = \App\Models\Department::orderBy('name')->get();

        // Otherwise return the view
        return view('users.index', compact('departments'));
    }

    /** Export data mahasiswa ke PDF (print-friendly, dibuka di tab baru) */
    public function exportPdf(Request $request)
    {
        $search = $request->get('search');

        $query = User::with(['department', 'kelas'])->where('role', 'user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->get();

        return view('users.export-pdf', compact('users'));
    }

    /** Import data mahasiswa dari file CSV (format: nim,name,email,phone,department_id,class_id) */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'file.mimes' => 'Format file harus berupa CSV (.csv / .txt). Silakan simpan spreadsheet Anda sebagai CSV terlebih dahulu.',
        ]);

        $path = $request->file('file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        if (empty($rows)) {
            return redirect()->route('users.index')->with('error', 'File CSV kosong.');
        }

        $header = array_map(function ($h) {
            return trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h));
        }, array_shift($rows));

        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (count($row) < 2 || ($row === [null]) || empty(trim(implode('', $row)))) {
                continue;
            }

            $padded = array_slice(array_pad($row, count($header), null), 0, count($header));
            $data = array_combine($header, $padded);

            $email = trim((string) ($data['email'] ?? ''));
            $nim = !empty($data['nim']) ? trim((string) $data['nim']) : null;

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
                || User::where('email', $email)->exists()
                || ($nim !== null && User::where('nim', $nim)->exists())) {
                $skipped++;
                continue;
            }

            User::create([
                'name'          => trim((string) ($data['name'] ?? '')) ?: 'Tanpa Nama',
                'email'         => $email,
                'nim'           => $nim,
                'phone'         => !empty($data['phone']) ? trim((string) $data['phone']) : null,
                'role'          => 'user',
                'password'      => Hash::make('password'),
                'department_id' => !empty($data['department_id']) ? (int) $data['department_id'] : null,
                'class_id'      => !empty($data['class_id']) ? (int) $data['class_id'] : null,
            ]);

            $imported++;
        }

        return redirect()->route('users.index')
            ->with('success', "Import selesai: {$imported} data berhasil ditambahkan, {$skipped} dilewati (duplikat/tidak valid).");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    $departments = \App\Models\Department::all();
    $kelasList = \App\Models\Kelas::with('department')->orderBy('semester')->orderBy('nomor_kelas')->get();
    return view('users.create', compact('departments', 'kelasList'));
}

public function store(Request $request)
{
    $request->validate([
        'name'          => 'required|string|max:255',
        'email'         => 'required|email|unique:users',
        'nim'           => 'nullable|string|unique:users',
        'phone'         => 'nullable|string',
        'role'          => 'required|in:admin,user',
        'password'      => 'required|min:6|confirmed',
        'department_id' => 'nullable|exists:departments,id',
        'class_id'      => 'nullable|exists:classes,id',
    ]);

    User::create([
        'name'          => $request->name,
        'email'         => $request->email,
        'nim'           => $request->nim,
        'phone'         => $request->phone,
        'role'          => $request->role,
        'password'      => bcrypt($request->password),
        'department_id' => $request->department_id,
        'class_id'      => $request->class_id,
    ]);

    return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
}

public function edit(User $user)
{
    $departments = \App\Models\Department::all();
    $kelasList = \App\Models\Kelas::with('department')
        ->orderBy('semester')
        ->orderBy('nomor_kelas')
        ->get();
    return view('users.edit', compact('user', 'departments', 'kelasList'));
}

public function update(Request $request, User $user)
{
    $request->validate([
        'name'          => 'required|string|max:255',
        'email'         => 'required|email|unique:users,email,'.$user->id,
        'nim'           => 'nullable|string|unique:users,nim,'.$user->id,
        'phone'         => 'nullable|string',
        'role'          => 'required|in:admin,user',
        'password'      => 'nullable|min:6|confirmed',
        'department_id' => 'nullable|exists:departments,id',
        'class_id'      => 'nullable|exists:classes,id',
    ]);

    $user->update([
        'name'          => $request->name,
        'email'         => $request->email,
        'nim'           => $request->nim,
        'phone'         => $request->phone,
        'role'          => $request->role,
        'department_id' => $request->department_id,
        'class_id'      => $request->class_id,
        'password'      => $request->filled('password') ? bcrypt($request->password) : $user->password,
    ]);

    return redirect()->route('users.index')->with('success', 'User berhasil diupdate');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}
