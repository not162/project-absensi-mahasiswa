<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        session(['captcha_result' => $num1 + $num2]);
        return view('auth.login', compact('num1', 'num2'));
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:6',
            'captcha_answer' => 'required|integer',
        ]);

        // Verify captcha
        if (session('captcha_result') === null || (int)$request->captcha_answer !== session('captcha_result')) {
            return redirect()->back()->withErrors(['captcha_answer' => 'Jawaban verifikasi salah!'])->withInput();
        }

        $username = $request->username;
        $password = $request->password;

        // Try to find the user by NIM (mahasiswa), NIP/kode_dosen (dosen), or email (admin)
        $user = \App\Models\User::where('nim', $username)
            ->orWhere('kode_dosen', $username)
            ->orWhere('email', $username)
            ->first();

        if ($user && Auth::attempt(['email' => $user->email, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->forget('captcha_result'); // clean up
            return redirect()->route('dashboard')->with('success', 'Login berhasil!');
        }

        return redirect()->back()->withErrors(['username' => 'NIP/NIM atau password salah.'])->withInput();
    }

    /**
     * Show the register form
     */
    public function showRegisterForm()
    {
        $departments = \App\Models\Department::orderBy('name')->get();
        $classes = \App\Models\Kelas::with('department')->orderBy('semester')->orderBy('nomor_kelas')->get();
        return view('auth.register', compact('departments', 'classes'));
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {


        // 2. Validate input
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|ends_with:@gmail.com',
            'password' => 'required|min:6',
            'role' => 'required|in:user,dosen,admin',
            'terms' => 'accepted',
            'department_id' => 'required_if:role,user,dosen|nullable|exists:departments,id',
            'class_id' => 'required_if:role,user|nullable|exists:classes,id',
        ];
        
        $messages = [
            'email.ends_with' => 'Email harus menggunakan domain @gmail.com pribadi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'role.in' => 'Peran yang dipilih tidak valid.',
            'terms.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
            'department_id.required_if' => 'Program Studi wajib dipilih.',
            'class_id.required_if' => 'Kelas wajib dipilih.',
        ];

        // Conditional validation based on role
        if ($request->role === 'user') {
            $rules['identifier'] = 'required|string|max:50|unique:users,nim';
            $messages['identifier.required'] = 'NIM wajib diisi untuk mahasiswa.';
            $messages['identifier.unique'] = 'NIM ini sudah terdaftar.';
        } elseif ($request->role === 'dosen') {
            $rules['identifier'] = 'required|string|max:50|unique:users,kode_dosen';
            $messages['identifier.required'] = 'NIP Dosen wajib diisi.';
            $messages['identifier.unique'] = 'NIP ini sudah terdaftar.';
        } elseif ($request->role === 'admin') {
            $rules['identifier'] = 'required|string';
            $messages['identifier.required'] = 'Kode Registrasi Admin wajib diisi.';
        }

        $validated = $request->validate($rules, $messages);

        // Security Check for Admin
        if ($validated['role'] === 'admin') {
            // Secret code constraint for admin registration (Format: admin-2026-[number])
            if (!preg_match('/^admin-2026-\d+$/i', $validated['identifier'])) {
                return redirect()->back()->withErrors(['identifier' => 'Kode Registrasi Admin tidak valid! Pendaftaran ditolak.'])->withInput();
            }
        }

        // 3. Create user mapping identifier based on role
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => $validated['role'],
            'department_id' => in_array($validated['role'], ['user', 'dosen']) ? $request->department_id : null,
            'class_id' => $validated['role'] === 'user' ? $request->class_id : null,
        ];

        if ($validated['role'] === 'user') {
            $userData['nim'] = $validated['identifier'];
        } elseif ($validated['role'] === 'dosen') {
            $userData['kode_dosen'] = $validated['identifier'];
        }

        $user = \App\Models\User::create($userData);

        // Auto-assign new lecturer to all courses in their department from semesters 1 to 8
        if ($user->role === 'dosen' && $user->department_id) {
            $courses = \App\Models\Course::where('department_id', $user->department_id)
                ->whereBetween('semester', [1, 8])
                ->get();
            foreach ($courses as $course) {
                \Illuminate\Support\Facades\DB::table('lecturer_courses')->updateOrInsert(
                    ['user_id' => $user->id, 'course_id' => $course->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // 4. Update a global "latest registration" cache or just simulate polling update
        // We can just rely on the User model timestamps if we want to poll it, 
        // but for now the user is created successfully.
        
        // Auto login
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registrasi berhasil! Selamat datang.');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Logout berhasil!');
    }
}
