// Khởi tạo kết nối WebSocket
const socket = io('http://localhost:3000', {
    reconnection: true,
    reconnectionAttempts: 5,
    reconnectionDelay: 1000,
    reconnectionDelayMax: 5000,
    timeout: 20000
});

// Biến để theo dõi trạng thái chỉnh sửa
let editingTimeouts = {};

// Xử lý sự kiện kết nối
socket.on('connect', () => {
    console.log('Đã kết nối WebSocket, ID:', socket.id);
    // Gửi thông tin người dùng
    socket.emit('identify', {
        email: userEmail,
        avatar: userAvatar
    });
});

// Xử lý sự kiện ngắt kết nối
socket.on('disconnect', (reason) => {
    console.log('Mất kết nối WebSocket:', reason);
});

// Xử lý lỗi kết nối
socket.on('connect_error', (error) => {
    console.error('Lỗi kết nối WebSocket:', error);
});

// Nhận thông báo có người đang chỉnh sửa
socket.on('note_editing', (data) => {
    showEditorInfo(data);
});

// Nhận thông báo ngừng chỉnh sửa
socket.on('note_editing_stopped', (data) => {
    hideEditorInfo(data.noteId);
});

// Nhận cập nhật ghi chú
socket.on('note_updated', (updatedNote) => {
    updateNoteInStorage(updatedNote);
});

// Nhận thông báo xóa ghi chú
socket.on('note_deleted', (data) => {
    deleteNoteFromStorage(data.noteId);
});

// Hàm gửi thông báo đang chỉnh sửa
function notifyEditing(noteId) {
    clearTimeout(editingTimeouts[noteId]);
    
    socket.emit('start_editing', {
        noteId,
        userEmail,
        userAvatar
    });

    editingTimeouts[noteId] = setTimeout(() => {
        socket.emit('stop_editing', { noteId });
    }, 2000);
}

// Hàm gửi cập nhật ghi chú
function sendNoteUpdate(note) {
    socket.emit('note_update', note);
}

// Hàm gửi thông báo xóa ghi chú
function sendNoteDeletion(noteId) {
    socket.emit('note_delete', { noteId });
}

// Hàm hiển thị thông tin người đang chỉnh sửa
function showEditorInfo(data) {
    const noteEl = document.querySelector(`[data-note-id="${data.noteId}"]`);
    if (noteEl) {
        let editorInfo = noteEl.querySelector('.editor-info');
        if (!editorInfo) {
            editorInfo = document.createElement('div');
            editorInfo.className = 'editor-info';
            noteEl.querySelector('.note-card').appendChild(editorInfo);
        }

        editorInfo.innerHTML = `
            <small class="text-muted">
                <img src="${data.userAvatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(data.userEmail)}`}" 
                     class="avatar-xs" 
                     title="${data.userEmail} đang chỉnh sửa">
                đang chỉnh sửa...
            </small>
        `;
    }
}

// Hàm ẩn thông tin người đang chỉnh sửa
function hideEditorInfo(noteId) {
    const noteEl = document.querySelector(`[data-note-id="${noteId}"]`);
    if (noteEl) {
        const editorInfo = noteEl.querySelector('.editor-info');
        if (editorInfo) {
            editorInfo.remove();
        }
    }
}

// Hàm cập nhật ghi chú trong storage
function updateNoteInStorage(updatedNote) {
    // Cập nhật trong notes của người dùng
    const index = notes.findIndex(n => n.id === updatedNote.id);
    if (index !== -1) {
        notes[index] = updatedNote;
        localStorage.setItem(notesKey, JSON.stringify(notes));
        renderNotes();
    } 
    // Cập nhật trong shared notes
    else {
        const sharedNotes = getAllSharedNotes();
        const sharedIndex = sharedNotes.findIndex(n => n.id === updatedNote.id);
        if (sharedIndex !== -1) {
            const sharedNoteKey = 'notes_' + updatedNote.owner;
            let ownerNotes = JSON.parse(localStorage.getItem(sharedNoteKey)) || [];
            const ownerIndex = ownerNotes.findIndex(n => n.id === updatedNote.id);
            if (ownerIndex !== -1) {
                ownerNotes[ownerIndex] = updatedNote;
                localStorage.setItem(sharedNoteKey, JSON.stringify(ownerNotes));
                renderNotes();
            }
        }
    }
}

// Hàm xóa ghi chú khỏi storage
function deleteNoteFromStorage(noteId) {
    const index = notes.findIndex(n => n.id === noteId);
    if (index !== -1) {
        notes.splice(index, 1);
        localStorage.setItem(notesKey, JSON.stringify(notes));
        renderNotes();
    }
}

// Style cho editor info
const style = document.createElement('style');
style.textContent = `
    .editor-info {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(255,255,255,0.9);
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
        z-index: 100;
    }
    .avatar-xs {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 4px;
    }
    .dark-mode .editor-info {
        background: rgba(36,37,38,0.9);
    }
`;
document.head.appendChild(style); 