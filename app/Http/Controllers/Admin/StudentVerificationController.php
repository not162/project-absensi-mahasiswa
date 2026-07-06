<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentVerificationController extends Controller
{
    public function assignClass(Request $request, User $student)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $student->update([
            'department_id' => $request->department_id,
            'class_id' => $request->class_id,
        ]);

        return redirect()->back()->with('success', "Mahasiswa {$student->name} berhasil diverifikasi dan ditempatkan ke kelas.");
    }
}
