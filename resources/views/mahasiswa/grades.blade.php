@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="fas fa-star text-warning me-2"></i>Nilai Saya</h2>
        <a href="{{ route('grades.my.pdf') }}" class="btn btn-primary shadow-sm" target="_blank">
            <i class="fas fa-file-pdf me-2"></i>Cetak KHS
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Kode</th>
                        <th>Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Semester</th>
                        <th>Tugas</th>
                        <th>UTS</th>
                        <th>UAS</th>
                        <th>Nilai Akhir</th>
                        <th>Huruf</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grades as $grade)
                    <tr>
                        <td><code>{{ $grade->course->kode_matkul ?? '-' }}</code></td>
                        <td>{{ $grade->course->nama_matkul ?? '-' }}</td>
                        <td class="text-center">{{ $grade->course->sks ?? '-' }}</td>
                        <td class="text-center">{{ $grade->course->semester ?? '-' }}</td>
                        <td class="text-center">{{ $grade->nilai_tugas ?? '-' }}</td>
                        <td class="text-center">{{ $grade->nilai_uts ?? '-' }}</td>
                        <td class="text-center">{{ $grade->nilai_uas ?? '-' }}</td>
                        <td class="text-center fw-bold">{{ $grade->nilai_akhir ?? '-' }}</td>
                        <td class="text-center">
                            @if($grade->grade_huruf)
                                <span class="badge bg-{{ in_array($grade->grade_huruf, ['A','AB','B']) ? 'success' : (in_array($grade->grade_huruf, ['BC','C']) ? 'warning' : 'danger') }}">
                                    {{ $grade->grade_huruf }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada nilai yang diinput.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
