<div class="card shadow-sm mt-3 mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fas fa-comments text-primary me-2"></i>Ruang Diskusi Live</h6>
        <span class="badge bg-primary rounded-pill" id="disc-count-{{ $assignment->id }}">0 Pesan</span>
    </div>
    <div class="card-body p-0">
        <div class="chat-box p-3 bg-light" id="chat-box-{{ $assignment->id }}" style="height: 300px; overflow-y: auto;">
            <div class="text-center text-muted small py-3" id="no-msg-{{ $assignment->id }}">Belum ada pesan diskusi.</div>
            <!-- Pesan akan di-render di sini -->
        </div>
    </div>
    <div class="card-footer bg-white">
        <form class="d-flex gap-2" id="form-chat-{{ $assignment->id }}" onsubmit="postMessage(event, {{ $assignment->id }})">
            @csrf
            <input type="text" id="input-msg-{{ $assignment->id }}" class="form-control" placeholder="Ketik pesan..." required>
            <button type="submit" class="btn btn-primary" id="btn-send-{{ $assignment->id }}">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    window.discussionEscHtml = window.discussionEscHtml || function(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    window.lastMsgIds = window.lastMsgIds || {};
    window.lastMsgIds[{{ $assignment->id }}] = 0;

    window.fetchMessages_{{ $assignment->id }} = function() {
        const assignmentId = {{ $assignment->id }};
        fetch(`{{ route('assignments.discussions.index', $assignment->id) }}?last_id=${window.lastMsgIds[assignmentId]}`)
            .then(res => res.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    const chatBox = document.getElementById(`chat-box-${assignmentId}`);
                    document.getElementById(`no-msg-${assignmentId}`)?.remove();

                    data.messages.forEach(msg => {
                        window.lastMsgIds[assignmentId] = msg.id;
                        const isMe = msg.user_id == {{ auth()->id() }};

                        const controlsHtml = isMe ? `
                            <div class="mt-2 text-end">
                                <a href="javascript:void(0)" onclick="editMessage(${assignmentId}, ${msg.id})" class="text-light small text-decoration-none me-2"><i class="fas fa-edit"></i> Edit</a>
                                <a href="javascript:void(0)" onclick="deleteMessage(${assignmentId}, ${msg.id})" class="text-light small text-decoration-none"><i class="fas fa-trash"></i> Hapus</a>
                            </div>
                        ` : '';

                        const msgHtml = `
                            <div class="d-flex mb-3 ${isMe ? 'justify-content-end' : ''}" id="msg-item-${msg.id}">
                                <div class="${isMe ? 'bg-primary text-white' : 'bg-white border'} rounded p-2 px-3 shadow-sm" style="max-width: 80%;">
                                    <div class="small fw-bold ${isMe ? 'text-light' : 'text-primary'} mb-1">
                                        ${isMe ? 'Anda' : window.discussionEscHtml(msg.name)}
                                        ${msg.role == 'dosen' && !isMe ? '<i class="fas fa-check-circle text-success ms-1" title="Dosen"></i>' : ''}
                                    </div>
                                    <div id="msg-text-${msg.id}">${window.discussionEscHtml(msg.message)}</div>
                                    ${controlsHtml}
                                    <div class="text-end mt-1" style="font-size: 0.7rem; opacity: 0.7;">${window.discussionEscHtml(msg.time)}</div>
                                </div>
                            </div>
                        `;
                        chatBox.insertAdjacentHTML('beforeend', msgHtml);
                    });

                    chatBox.scrollTop = chatBox.scrollHeight;

                    const countBadge = document.getElementById(`disc-count-${assignmentId}`);
                    countBadge.innerText = (parseInt(countBadge.innerText) + data.messages.length) + " Pesan";
                }
            })
            .catch(err => console.error(err));
    };

    window.postMessage = window.postMessage || function(e, assignmentId) {
        e.preventDefault();
        const input = document.getElementById(`input-msg-${assignmentId}`);
        const btn = document.getElementById(`btn-send-${assignmentId}`);
        const msg = input.value.trim();

        if (!msg) return;

        btn.disabled = true;

        const editId = input.dataset.editId;
        const method = editId ? 'PUT' : 'POST';
        const url = editId
            ? `{{ url('/api/assignments') }}/${assignmentId}/discussions/${editId}`
            : `{{ url('/api/assignments') }}/${assignmentId}/discussions`;

        fetch(url, {
            method: method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: msg })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                delete input.dataset.editId;

                if (editId) {
                    const textEl = document.getElementById(`msg-text-${editId}`);
                    if (textEl) textEl.innerText = msg;
                } else if (typeof window[`fetchMessages_${assignmentId}`] === 'function') {
                    window[`fetchMessages_${assignmentId}`]();
                }
            }
        })
        .finally(() => {
            btn.disabled = false;
            input.focus();
        });
    };

    window.editMessage = window.editMessage || function(assignmentId, id) {
        const input = document.getElementById(`input-msg-${assignmentId}`);
        const textEl = document.getElementById(`msg-text-${id}`);
        if (!input || !textEl) return;
        input.value = textEl.textContent;
        input.dataset.editId = id;
        input.focus();
    };

    window.deleteMessage = window.deleteMessage || function(assignmentId, id) {
        if (!confirm('Hapus pesan ini?')) return;
        fetch(`{{ url('/api/assignments') }}/${assignmentId}/discussions/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`msg-item-${id}`)?.remove();
                const countBadge = document.getElementById(`disc-count-${assignmentId}`);
                if (countBadge) {
                    countBadge.innerText = (Math.max(0, parseInt(countBadge.innerText) - 1)) + " Pesan";
                }
            }
        });
    };

    document.addEventListener("DOMContentLoaded", function() {
        window.fetchMessages_{{ $assignment->id }}();
        setInterval(window.fetchMessages_{{ $assignment->id }}, 3000);
    });
</script>
@endpush
