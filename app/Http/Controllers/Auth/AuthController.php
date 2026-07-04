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
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'role' => 'required|in:user,dosen,admin',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', 'Login berhasil!');
        }

        return redirect()->back()->with('error', 'Email, password, atau role salah');
    }

    /**
     * Show the register form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
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
            'terms' => 'accepted'
        ];
        
        $messages = [
            'email.ends_with' => 'Email harus menggunakan domain @gmail.com pribadi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'role.in' => 'Peran yang dipilih tidak valid.',
            'terms.accepted' => 'Anda harus menyetujui syarat dan ketentuan.'
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
            // Secret code constraint for admin registration (Customizable via .env)
            $secretCode = env('ADMIN_REGISTRATION_CODE', 'ADMIN-RAHASIA-123');
            if ($validated['identifier'] !== $secretCode) {
                return redirect()->back()->withErrors(['identifier' => 'Kode Registrasi Admin tidak valid! Pendaftaran ditolak.'])->withInput();
            }
        }

        // 3. Create user mapping identifier based on role
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => $validated['role'],
            'department_id' => null,
            'class_id' => null,
        ];

        if ($validated['role'] === 'user') {
            $userData['nim'] = $validated['identifier'];
        } elseif ($validated['role'] === 'dosen') {
            $userData['kode_dosen'] = $validated['identifier'];
        }

        $user = \App\Models\User::create($userData);

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
