<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        return view('mahasiswa.profil', ['user' => Auth::user()]);
    }

    public function edit()
    {
        return view('mahasiswa.profil-edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = [
            'name'  => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ];

        // Handle photo deletion request
        if ($request->delete_photo == '1') {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
                $data['photo'] = null;
                $user->photo = null;
            }
        }

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $path = $request->file('photo')->store('photos', 'public');
            $data['photo'] = $path;
        }

        $user->update($data);

        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui');
    }

    public function editPassword()
    {
        return view('mahasiswa.profil-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('profile.show')->with('success', 'Password berhasil diubah');
    }
}
