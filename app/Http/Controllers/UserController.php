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

        // Otherwise return the view
        return view('users.index');
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

    /** Import data mahasiswa dari file CSV/Excel (format: nim,name,email,phone,department_id,class_id) */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $path = $request->file('file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', array_shift($rows));

        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (count($row) < 2 || empty(trim($row[0] ?? ''))) {
                continue;
            }

            $data = array_combine($header, array_pad($row, count($header), null));

            if (empty($data['email']) || User::where('email', $data['email'])->exists()
                || (!empty($data['nim']) && User::where('nim', $data['nim'])->exists())) {
                $skipped++;
                continue;
            }

            User::create([
                'name'          => $data['name'] ?? 'Tanpa Nama',
                'email'         => $data['email'],
                'nim'           => $data['nim'] ?? null,
                'phone'         => $data['phone'] ?? null,
                'role'          => 'user',
                'password'      => Hash::make('password'),
                'department_id' => $data['department_id'] ?? null,
                'class_id'      => $data['class_id'] ?? null,
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
        'password'      => 'required|min:6',
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
        'password'      => $request->password ? bcrypt($request->password) : $user->password,
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
