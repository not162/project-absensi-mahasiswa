<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserService
{
    /**
     * Get users table data using Yajra DataTables (Server-Side Rendering)
     * This method resolves N+1 query problems by using Eager Loading (Big O optimization)
     */
    public function getUsersDataTable(Request $request)
    {
        // 1. Resolve N+1 issue: Eager load relations
        // Big O optimization: Loads all related departments/classes in 2 extra queries
        // rather than N queries for N users.
        $query = User::with(['department', 'kelas.department'])->where('role', 'user');

        // 2. Return Server-Side processing JSON to DataTables
        return DataTables::eloquent($query)
            ->addColumn('action', function ($user) {
                // Return HTML action buttons
                $editUrl = route('users.edit', $user);
                $deleteUrl = route('users.destroy', $user);
                $csrf = csrf_field();
                $method = method_field('DELETE');
                
                return '
                    <a href="'.$editUrl.'" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="'.$deleteUrl.'" method="POST" class="form-delete" style="display:inline">
                        '.$csrf.'
                        '.$method.'
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                ';
            })
            ->editColumn('department', function ($user) {
                return $user->department ? $user->department->name : '-';
            })
            ->editColumn('kelas', function ($user) {
                if ($user->kelas) {
                    $deptName = $user->kelas->department ? $user->kelas->department->name : '';
                    return $user->kelas->nomor_kelas . ' - ' . $deptName . ' (Smt ' . $user->kelas->semester . ')';
                }
                return '-';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
