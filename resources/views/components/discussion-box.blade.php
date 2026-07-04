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
    let lastMsgId_{{ $assignment->id }} = 0;
    
    function fetchMessages_{{ $assignment->id }}() {
        fetch(`{{ route('assignments.discussions.index', $assignment->id) }}?last_id=${lastMsgId_{{ $assignment->id }}}`)
            .then(res => res.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    let chatBox = document.getElementById('chat-box-{{ $assignment->id }}');
                    document.getElementById('no-msg-{{ $assignment->id }}')?.remove();
                    
                    data.messages.forEach(msg => {
                        lastMsgId_{{ $assignment->id }} = msg.id;
                        let isMe = msg.user_id == {{ auth()->id() }};
                        
                        let controlsHtml = isMe ? `
                            <div class="mt-2 text-end">
                                <a href="javascript:void(0)" onclick="editMessage(${msg.id}, '${msg.message.replace(/'/g, "\\'")}')" class="text-light small text-decoration-none me-2"><i class="fas fa-edit"></i> Edit</a>
                                <a href="javascript:void(0)" onclick="deleteMessage(${msg.id})" class="text-light small text-decoration-none"><i class="fas fa-trash"></i> Hapus</a>
                            </div>
                        ` : '';

                        let msgHtml = `
                            <div class="d-flex mb-3 ${isMe ? 'justify-content-end' : ''}" id="msg-item-${msg.id}">
                                <div class="${isMe ? 'bg-primary text-white' : 'bg-white border'} rounded p-2 px-3 shadow-sm" style="max-width: 80%;">
                                    <div class="small fw-bold ${isMe ? 'text-light' : 'text-primary'} mb-1">
                                        ${isMe ? 'Anda' : msg.name} 
                                        ${msg.role == 'dosen' && !isMe ? '<i class="fas fa-check-circle text-success ms-1" title="Dosen"></i>' : ''}
                                    </div>
                                    <div id="msg-text-${msg.id}">${msg.message}</div>
                                    ${controlsHtml}
                                    <div class="text-end mt-1" style="font-size: 0.7rem; opacity: 0.7;">${msg.time}</div>
                                </div>
                            </div>
                        `;
                        chatBox.insertAdjacentHTML('beforeend', msgHtml);
                    });
                    
                    chatBox.scrollTop = chatBox.scrollHeight;
                    
                    let countBadge = document.getElementById('disc-count-{{ $assignment->id }}');
                    // Reset count (we could re-calculate or just poll total, but appending length works if we don't delete. If delete happens, a full reload is better. For now we just add length)
                    countBadge.innerText = (parseInt(countBadge.innerText) + data.messages.length) + " Pesan";
                }
            })
            .catch(err => console.error(err));
    }

    function postMessage(e, assignmentId) {
        e.preventDefault();
        let input = document.getElementById(`input-msg-${assignmentId}`);
        let btn = document.getElementById(`btn-send-${assignmentId}`);
        let msg = input.value.trim();
        
        if (!msg) return;
        
        btn.disabled = true;
        
        // Cek jika sedang dalam mode edit
        let editId = input.dataset.editId;
        let method = editId ? 'PUT' : 'POST';
        let url = editId 
            ? `{{ url('/api/assignments') }}/${assignmentId}/discussions/${editId}` 
            : `{{ route('assignments.discussions.store', $assignment->id) }}`;

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: msg })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                delete input.dataset.editId; // exit edit mode
                
                if(editId) {
                    // Update text locally without waiting for poll
                    document.getElementById(`msg-text-${editId}`).innerText = msg;
                } else {
                    fetchMessages_{{ $assignment->id }}(); // Segera tarik pesan
                }
            }
        })
        .finally(() => {
            btn.disabled = false;
            input.focus();
        });
    }

    function editMessage(id, text) {
        let input = document.getElementById(`input-msg-{{ $assignment->id }}`);
        input.value = text;
        input.dataset.editId = id; // set mode edit
        input.focus();
    }

    function deleteMessage(id) {
        if(!confirm('Hapus pesan ini?')) return;
        fetch(`{{ url('/api/assignments') }}/{{ $assignment->id }}/discussions/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById(`msg-item-${id}`)?.remove();
                let countBadge = document.getElementById('disc-count-{{ $assignment->id }}');
                countBadge.innerText = (Math.max(0, parseInt(countBadge.innerText) - 1)) + " Pesan";
            }
        });
    }

    // Mulai polling setiap 3 detik
    document.addEventListener("DOMContentLoaded", function() {
        fetchMessages_{{ $assignment->id }}();
        setInterval(fetchMessages_{{ $assignment->id }}, 3000);
    });
</script>
@endpush
