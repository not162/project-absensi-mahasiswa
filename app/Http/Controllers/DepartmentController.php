<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::withCount('users')->paginate(15);
        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:departments',
            'description' => 'nullable|string',
        ]);

        Department::create($validated);

        return redirect()->route('departments.index')->with('success', 'Program Studi berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        $department->load('users');
        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete department with users');
        }

        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully');
    }
}
