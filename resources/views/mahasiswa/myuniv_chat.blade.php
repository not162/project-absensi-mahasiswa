@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-primary"><i class="fas fa-brain me-2"></i>MyUniv AI Advisor</h2>
            <p class="text-muted mb-0">Teman belajar pintar & penasihat akademis real-time Anda.</p>
        </div>
        <a href="{{ route('myuniv.ai', ['reset' => 1]) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus seluruh riwayat percakapan?')">
            <i class="fas fa-redo-alt me-1"></i> Reset Chat
        </a>
    </div>

    <div class="row g-4">
        {{-- Left: Academic Classification & Data Science Dashboard --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-chart-pie text-primary me-2"></i>Analisis Prediksi Kelulusan (ML)
                    </h5>
                    <small class="text-muted">Hasil evaluasi data science murni Laravel PHP</small>
                </div>
                <div class="card-body">
                    {{-- Circular/Horizontal Risk Meter --}}
                    <div class="text-center py-3">
                        @php
                            $score = $analysis['prediction']['risk_score'];
                            $level = $analysis['prediction']['risk_level'];
                            $color = $analysis['prediction']['risk_color'];
                            $rec = $analysis['prediction']['recommendation'];
                            $reasons = $analysis['prediction']['reasons'];
                        @endphp
                        
                        <div class="d-inline-flex flex-column align-items-center justify-content-center p-4 rounded-circle border border-5 border-{{ $color }} shadow-sm mb-3" style="width: 140px; height: 140px;">
                            <span class="fs-4 fw-extrabold text-{{ $color }}">{{ $score }}%</span>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 9px;">Skor Risiko</small>
                        </div>
                        <h4 class="fw-extrabold text-{{ $color }} mb-1">{{ $level }}</h4>
                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} px-3 py-1 rounded-pill small">
                            {{ $level === 'AT RISK' ? 'Risiko Kegagalan Tinggi' : ($level === 'GOOD' ? 'Normal / Perlu Perbaikan' : 'Luar Biasa / Aman') }}
                        </span>
                    </div>

                    {{-- Recommendation Text --}}
                    <div class="p-3 bg-light rounded-3 mb-4">
                        <h6 class="fw-bold text-dark mb-1 small"><i class="fas fa-lightbulb text-warning me-1"></i> Rekomendasi Sistem:</h6>
                        <p class="text-muted small mb-0" style="line-height: 1.5;">{{ $rec }}</p>
                    </div>

                    {{-- Risk Factors --}}
                    @if(count($reasons) > 0)
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark mb-2 small"><i class="fas fa-exclamation-triangle text-danger me-1"></i> Faktor Pemicu Risiko:</h6>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                @foreach($reasons as $reason)
                                    <li class="small text-muted d-flex align-items-start gap-2">
                                        <i class="fas fa-times-circle text-danger mt-1"></i>
                                        <span>{{ $reason }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Real-time Stats Cards --}}
                    <div>
                        <h6 class="fw-bold text-dark mb-3 small"><i class="fas fa-tasks text-primary me-1"></i> Kinerja Semester Ini:</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="border rounded p-2 text-center bg-white">
                                    <small class="text-muted d-block" style="font-size: 10px;">Kehadiran</small>
                                    <strong class="text-dark">{{ $analysis['metrics']['attendance_rate'] }}%</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center bg-white">
                                    <small class="text-muted d-block" style="font-size: 10px;">Pengumpulan Tugas</small>
                                    <strong class="text-dark">{{ $analysis['metrics']['submission_rate'] }}%</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center bg-white">
                                    <small class="text-muted d-block" style="font-size: 10px;">Rata Nilai Tugas</small>
                                    <strong class="text-dark">{{ $analysis['metrics']['avg_assignment_grade'] }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center bg-white">
                                    <small class="text-muted d-block" style="font-size: 10px;">Mata Kuliah Mengulang</small>
                                    <strong class="text-dark">{{ $analysis['metrics']['total_repeats'] }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Right: Chatbot Panel --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm d-flex flex-column" style="height: 600px;">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Chat dengan MyUniv</h5>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="text-success small d-flex align-items-center gap-1" style="font-size: 11px;">
                                    <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px;"></span>
                                    Online &amp; RAG Llama-3.3
                                </span>
                                <span class="badge bg-info text-white font-monospace d-none" id="rlhfScoreBadge">
                                    RLHF Alignment: {{ round($user->rlhf_score, 2) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Chat Bubbles Box --}}
                <div class="card-body bg-light overflow-auto p-4 flex-grow-1" id="chatBox" style="min-height: 250px;">
                    @if(count($chatHistory) === 0)
                        <div class="text-center py-5 text-muted">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center p-3 mb-3">
                                <i class="fas fa-comments text-primary fs-3"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Halo, {{ $user->name }}!</h5>
                            <p class="small mx-auto" style="max-width: 400px;">
                                Saya adalah MyUniv AI. Saya telah menganalisis aktivitas akademis Anda semester ini. Silakan klik tombol di bawah untuk rekomendasi langsung atau ketik pertanyaan Anda!
                            </p>
                        </div>
                    @else
                        @foreach($chatHistory as $msg)
                            @if($msg['role'] === 'user')
                                <div class="d-flex justify-content-end mb-3">
                                    <div class="bg-primary text-white p-3 rounded-3 shadow-sm" style="max-width: 75%;">
                                        <p class="mb-0 small" style="white-space: pre-wrap;">{{ $msg['content'] }}</p>
                                        <span class="d-block text-end mt-1 text-white-50" style="font-size: 9px;">{{ $msg['time'] ?? now()->format('H:i') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="d-flex justify-content-start mb-3 gap-2">
                                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                                        <i class="fas fa-robot" style="font-size: 13px;"></i>
                                    </div>
                                    <div class="bg-white text-dark p-3 rounded-3 border shadow-sm" style="max-width: 75%;">
                                        <div class="mb-0 small markdown-content" style="white-space: pre-wrap;">{{ $msg['content'] }}</div>
                                        <span class="d-block mt-1 text-muted" style="font-size: 9px;">{{ $msg['time'] ?? now()->format('H:i') }}</span>
                                        
                                        {{-- Feedback buttons --}}
                                        <div class="mt-2 pt-1 border-top d-flex gap-2 justify-content-end align-items-center feedback-bar">
                                            <button type="button" class="btn btn-link p-0 text-success small" onclick="submitRlhfFeedback(this, 'terimakasih')" title="Puas / Terima Kasih (+1.5%)"><i class="far fa-thumbs-up"></i></button>
                                            <button type="button" class="btn btn-link p-0 text-warning small" onclick="submitRlhfFeedback(this, 'tidak_cukup_puas')" title="Tidak Cukup Puas (-0.18%)"><i class="far fa-meh"></i></button>
                                            <button type="button" class="btn btn-link p-0 text-danger small" onclick="submitRlhfFeedback(this, 'kurang_puas')" title="Kurang Puas (-2.1%)"><i class="far fa-thumbs-down"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                {{-- Action Buttons Bar --}}
                <div class="p-2 border-top bg-white d-flex flex-wrap gap-2 justify-content-center">
                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill text-xs px-3" onclick="sendQuickMessage('Berikan rekomendasi tips belajar terbaik untuk saya berdasarkan performa akademik saya.')">
                        💡 Tips Belajar
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill text-xs px-3" onclick="sendQuickMessage('Bagaimana strategi agar mendapat nilai bagus dalam test teori?')">
                        📝 Tips Ujian Teori
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill text-xs px-3" onclick="sendQuickMessage('Tunjukkan panduan dan struktur folder standard untuk membuat best-practice project.')">
                        💻 Panduan Project
                    </button>
                </div>

                {{-- Input Form --}}
                <div class="card-footer bg-white border-top p-3">
                    <form id="chatForm" class="d-flex gap-2">
                        @csrf
                        <input type="text" id="messageInput" class="form-control text-dark" placeholder="Tanyakan saran akademik atau tips belajar kepada MyUniv AI..." required autocomplete="off">
                        <button type="submit" class="btn btn-primary px-4 fw-bold text-white d-flex align-items-center gap-2" id="btnSend">
                            <span>Kirim</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .fw-extrabold { font-weight: 800; }
    .text-xs { font-size: 0.8rem; }
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
    .markdown-content ul, .markdown-content ol {
        margin-bottom: 0;
        padding-left: 20px;
    }
</style>

@push('scripts')
<script>
    const chatBox = document.getElementById('chatBox');
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const btnSend = document.getElementById('btnSend');

    // Scroll to bottom of chat
    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
    scrollToBottom();

    // Send Quick Prompt Suggestion
    function sendQuickMessage(text) {
        messageInput.value = text;
        chatForm.dispatchEvent(new Event('submit'));
    }

    // Submit Chat Form
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = messageInput.value.trim();
        if(!msg) return;

        // Clear input & disable buttons
        messageInput.value = '';
        messageInput.disabled = true;
        btnSend.disabled = true;

        // Remove welcome text if it exists
        const welcomeDiv = chatBox.querySelector('.text-center.py-5');
        if(welcomeDiv) {
            chatBox.innerHTML = '';
        }

        // Append user bubble
        const userTime = new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
        const userBubble = `
            <div class="d-flex justify-content-end mb-3">
                <div class="bg-primary text-white p-3 rounded-3 shadow-sm" style="max-width: 75%;">
                    <p class="mb-0 small" style="white-space: pre-wrap;">${escapeHtml(msg)}</p>
                    <span class="d-block text-end mt-1 text-white-50" style="font-size: 9px;">${userTime}</span>
                </div>
            </div>
        `;
        chatBox.insertAdjacentHTML('beforeend', userBubble);
        scrollToBottom();

        // Append typing indicator
        const typingBubble = `
            <div class="d-flex justify-content-start mb-3 gap-2" id="typingIndicator">
                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                    <i class="fas fa-robot" style="font-size: 13px;"></i>
                </div>
                <div class="bg-white text-dark p-3 rounded-3 border shadow-sm" style="max-width: 75%;">
                    <div class="small d-flex align-items-center gap-1">
                        <i class="fas fa-spinner fa-spin text-primary me-1"></i>
                        <span>MyUniv sedang menganalisis data Anda...</span>
                    </div>
                </div>
            </div>
        `;
        chatBox.insertAdjacentHTML('beforeend', typingBubble);
        scrollToBottom();

        // Fetch API
        fetch("{{ route('myuniv.ai.chat') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: msg })
        })
        .then(res => res.json())
        .then(data => {
            // Remove typing indicator
            const typing = document.getElementById('typingIndicator');
            if(typing) typing.remove();

            if (data.success) {
                // Update RLHF Score badge dynamically
                if (data.rlhf_score !== undefined) {
                    document.getElementById('rlhfScoreBadge').innerText = `RLHF Alignment: ${data.rlhf_score}%`;
                }

                // Append AI bubble with feedback buttons
                const aiBubble = `
                    <div class="d-flex justify-content-start mb-3 gap-2">
                        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                            <i class="fas fa-robot" style="font-size: 13px;"></i>
                        </div>
                        <div class="bg-white text-dark p-3 rounded-3 border shadow-sm" style="max-width: 75%;">
                            <div class="mb-0 small markdown-content" style="white-space: pre-wrap;">${data.response}</div>
                            <span class="d-block mt-1 text-muted" style="font-size: 9px;">${data.time}</span>
                            <div class="mt-2 pt-1 border-top d-flex gap-2 justify-content-end align-items-center feedback-bar">
                                <button type="button" class="btn btn-link p-0 text-success small" onclick="submitRlhfFeedback(this, 'terimakasih')" title="Puas / Terima Kasih (+1.5%)"><i class="far fa-thumbs-up"></i></button>
                                <button type="button" class="btn btn-link p-0 text-warning small" onclick="submitRlhfFeedback(this, 'tidak_cukup_puas')" title="Tidak Cukup Puas (-0.18%)"><i class="far fa-meh"></i></button>
                                <button type="button" class="btn btn-link p-0 text-danger small" onclick="submitRlhfFeedback(this, 'kurang_puas')" title="Kurang Puas (-2.1%)"><i class="far fa-thumbs-down"></i></button>
                            </div>
                        </div>
                    </div>
                `;
                chatBox.insertAdjacentHTML('beforeend', aiBubble);
                scrollToBottom();
            } else {
                alert('Gagal mendapatkan respon AI.');
            }
        })
        .catch(err => {
            console.error(err);
            const typing = document.getElementById('typingIndicator');
            if(typing) typing.remove();
            alert('Terjadi kesalahan jaringan.');
        })
        .finally(() => {
            messageInput.disabled = false;
            btnSend.disabled = false;
            messageInput.focus();
        });
    });

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Submit explicit RLHF Feedback
    function submitRlhfFeedback(btn, feedbackType) {
        const bar = btn.closest('.feedback-bar');
        const buttons = bar.querySelectorAll('button');
        buttons.forEach(b => b.disabled = true);

        fetch("{{ route('myuniv.ai.rlhf') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ type: feedbackType })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                
                // Show success status
                bar.innerHTML = `<span class="text-success" style="font-size: 9px;"><i class="fas fa-check-circle me-1"></i> Terima kasih atas feedback Anda!</span>`;
            } else {
                buttons.forEach(b => b.disabled = false);
            }
        })
        .catch(err => {
            console.error(err);
            buttons.forEach(b => b.disabled = false);
        });
    }
</script>
@endpush
@endsection
