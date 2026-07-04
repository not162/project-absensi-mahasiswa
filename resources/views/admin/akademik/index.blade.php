@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Data Akademik</h1>

    <div class="row g-3">
        @for($i = 1; $i <= 8; $i++)
        <div class="col-md-3">
            <a href="{{ route('akademik.semester', $i) }}" class="text-decoration-none">
                <div class="card text-center py-4 shadow-sm" style="border-radius:12px; border-left: 5px solid #6366f1;">
                    <div class="card-body">
                        <h2 class="fw-bold text-primary">{{ $i }}</h2>
                        <p class="mb-0 text-muted">Semester {{ $i }}</p>
                    </div>
                </div>
            </a>
        </div>
        @endfor
    </div>
</div>
@endsection