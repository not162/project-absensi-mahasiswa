@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar Timer & Navigasi -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm sticky-top" style="top: 80px;">
                <div class="card-header bg-dark text-white text-center">
                    <h5 class="mb-0">Sisa Waktu</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-5 fw-bold text-danger" id="timerDisplay">00:00:00</div>
                    <hr>
                    <div class="d-flex flex-wrap gap-2 justify-content-center" id="questionNav">
                        @foreach($questions as $idx => $q)
                            <button type="button" class="btn btn-outline-secondary btn-sm nav-btn" data-target="q-{{ $q->id }}" id="nav-btn-{{ $q->id }}">
                                {{ $idx + 1 }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Area Soal -->
        <div class="col-md-9">
            <form action="{{ route('stem.submit', $attempt->id) }}" method="POST" id="examForm">
                @csrf
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Soal Ujian</h5>
                        <span class="badge bg-primary">STEM</span>
                    </div>
                    <div class="card-body p-0">
                        @foreach($questions as $idx => $q)
                            <div class="question-container p-4 border-bottom {{ $idx == 0 ? 'd-block' : 'd-none' }}" id="q-{{ $q->id }}">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5 class="fw-bold">Pertanyaan {{ $idx + 1 }}</h5>
                                    <span class="badge bg-secondary">Kategori: {{ $q->category }}</span>
                                </div>
                                <p class="fs-5">{{ $q->question_text }}</p>
                                
                                <div class="list-group">
                                    <label class="list-group-item list-group-item-action">
                                        <input class="form-check-input me-2 ans-radio" type="radio" name="answers[{{ $q->id }}]" value="A" data-qid="{{ $q->id }}">
                                        A. {{ $q->opt_a }}
                                    </label>
                                    <label class="list-group-item list-group-item-action">
                                        <input class="form-check-input me-2 ans-radio" type="radio" name="answers[{{ $q->id }}]" value="B" data-qid="{{ $q->id }}">
                                        B. {{ $q->opt_b }}
                                    </label>
                                    <label class="list-group-item list-group-item-action">
                                        <input class="form-check-input me-2 ans-radio" type="radio" name="answers[{{ $q->id }}]" value="C" data-qid="{{ $q->id }}">
                                        C. {{ $q->opt_c }}
                                    </label>
                                    <label class="list-group-item list-group-item-action">
                                        <input class="form-check-input me-2 ans-radio" type="radio" name="answers[{{ $q->id }}]" value="D" data-qid="{{ $q->id }}">
                                        D. {{ $q->opt_d }}
                                    </label>
                                </div>
                                
                                <div class="mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary btn-prev" {{ $idx == 0 ? 'disabled' : '' }}>Sebelumnya</button>
                                    @if($idx == count($questions) - 1)
                                        <button type="button" class="btn btn-success" onclick="confirmSubmit()">Selesai & Kumpulkan</button>
                                    @else
                                        <button type="button" class="btn btn-primary btn-next">Selanjutnya</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let timeLeft = {{ $timeRemaining }};
    const timerDisplay = document.getElementById('timerDisplay');
    const examForm = document.getElementById('examForm');

    function updateTimer() {
        let h = Math.floor(timeLeft / 3600);
        let m = Math.floor((timeLeft % 3600) / 60);
        let s = timeLeft % 60;
        
        timerDisplay.innerHTML = 
            (h < 10 ? "0" : "") + h + ":" + 
            (m < 10 ? "0" : "") + m + ":" + 
            (s < 10 ? "0" : "") + s;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            alert("Waktu habis! Ujian akan disubmit otomatis.");
            examForm.submit();
        }
        timeLeft--;
    }

    let timerInterval = setInterval(updateTimer, 1000);
    updateTimer();

    // Navigasi soal
    const questions = document.querySelectorAll('.question-container');
    const navBtns = document.querySelectorAll('.nav-btn');

    function showQuestion(id) {
        questions.forEach(q => q.classList.replace('d-block', 'd-none'));
        document.getElementById(id).classList.replace('d-none', 'd-block');
    }

    navBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            showQuestion(this.dataset.target);
        });
    });

    // Next / Prev buttons
    document.querySelectorAll('.btn-next').forEach(btn => {
        btn.addEventListener('click', function() {
            let current = this.closest('.question-container');
            let next = current.nextElementSibling;
            if (next && next.classList.contains('question-container')) {
                showQuestion(next.id);
            }
        });
    });

    document.querySelectorAll('.btn-prev').forEach(btn => {
        btn.addEventListener('click', function() {
            let current = this.closest('.question-container');
            let prev = current.previousElementSibling;
            if (prev && prev.classList.contains('question-container')) {
                showQuestion(prev.id);
            }
        });
    });

    // Tandai tombol navigasi jika sudah dijawab
    document.querySelectorAll('.ans-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            let qid = this.dataset.qid;
            let navBtn = document.getElementById('nav-btn-' + qid);
            navBtn.classList.replace('btn-outline-secondary', 'btn-primary');
        });
    });
});

function confirmSubmit() {
    if (confirm('Apakah Anda yakin sudah selesai dan ingin mengumpulkan jawaban?')) {
        document.getElementById('examForm').submit();
    }
}
</script>
@endsection
