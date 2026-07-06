@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <!-- Header -->
                <div class="card-header bg-primary text-white p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Simulasi Ujian TOEFL</h4>
                        <p class="mb-0 small text-white-50">Jawab semua pertanyaan dengan teliti.</p>
                    </div>
                    <div class="bg-white text-primary px-3 py-2 rounded-3 fw-bold shadow-sm">
                        <i class="fas fa-clock me-1 text-danger"></i> <span id="timer">15:00</span>
                    </div>
                </div>

                <!-- Form -->
                <form id="toeflForm" action="{{ route('mahasiswa.toefl.submit') }}" method="POST">
                    @csrf
                    <div class="card-body p-5">
                        
                        @php
                            $sections = [
                                'listening' => 'Section 1: Listening Comprehension',
                                'structure' => 'Section 2: Structure & Written Expression',
                                'reading' => 'Section 3: Reading Comprehension'
                            ];
                            $currentSection = '';
                        @endphp

                        @foreach($questions as $index => $q)
                            @if($currentSection !== $q['section'])
                                @php $currentSection = $q['section']; @endphp
                                <div class="alert alert-secondary fw-bold rounded-3 mb-4 mt-3">
                                    <i class="fas fa-book-open me-2 text-primary"></i> {{ $sections[$q['section']] }}
                                </div>
                            @endif

                            <div class="mb-5 p-4 border rounded-3 bg-white hover-shadow-sm transition-all">
                                <div class="d-flex mb-3">
                                    <span class="badge bg-primary me-2 align-self-start py-2">Soal {{ $index + 1 }}</span>
                                    <span class="badge bg-light text-dark align-self-start py-2 border text-capitalize">{{ $q['section'] }}</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-4 leading-relaxed" style="font-size: 1.1rem;">
                                    {!! nl2br(e($q['question'])) !!}
                                </h6>

                                <div class="options-container">
                                    @foreach(['A' => $q['opt_a'], 'B' => $q['opt_b'], 'C' => $q['opt_c'], 'D' => $q['opt_d']] as $key => $opt)
                                        <div class="form-check option-item p-3 border rounded-3 mb-3 hover-bg-light cursor-pointer">
                                            <input class="form-check-input ms-1 me-3" type="radio" name="answers[{{ $q['id'] }}]" id="q-{{ $q['id'] }}-{{ $key }}" value="{{ $key }}" required>
                                            <label class="form-check-label w-100 cursor-pointer text-secondary fw-medium" for="q-{{ $q['id'] }}-{{ $key }}">
                                                <strong>{{ $key }}.</strong> {{ $opt }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold rounded-3 shadow">
                                <i class="fas fa-paper-plane me-2"></i> Submit Jawaban Ujian
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .option-item:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1 !important;
    }
    .form-check-input:checked ~ .form-check-label {
        color: #0f172a !important;
        font-weight: 600 !important;
    }
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .option-item {
        transition: all 0.2s ease-in-out;
    }
</style>
@endpush

@push('scripts')
<script>
    // 15 Minutes timer
    let timeRemaining = 15 * 60;
    const timerElement = document.getElementById('timer');
    const formElement = document.getElementById('toeflForm');

    const countdown = setInterval(() => {
        let minutes = Math.floor(timeRemaining / 60);
        let seconds = timeRemaining % 60;

        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;

        timerElement.textContent = `${minutes}:${seconds}`;

        if (timeRemaining <= 0) {
            clearInterval(countdown);
            alert('Waktu ujian Anda telah habis! Sistem akan mengumpulkan jawaban Anda secara otomatis.');
            formElement.submit();
        }

        timeRemaining--;
    }, 1000);
</script>
@endpush
@endsection
