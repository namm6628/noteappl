const WebSocket = require('ws');
require('dotenv').config();

const port = process.env.PORT || 8080;
const wss = new WebSocket.Server({ port });

// Lưu trữ kết nối theo noteId
const noteConnections = new Map();

wss.on('connection', (ws) => {
    console.log('Client connected');
    
    // Xử lý tin nhắn từ client
    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);
            
            switch (data.type) {
                case 'join_note':
                    // Khi user tham gia vào một note
                    handleJoinNote(ws, data.noteId);
                    break;
                    
                case 'note_update':
                    // Khi user cập nhật nội dung note
                    handleNoteUpdate(ws, data);
                    break;
                    
                case 'cursor_position':
                    // Khi user di chuyển con trỏ (để hiển thị vị trí của người khác)
                    handleCursorPosition(ws, data);
                    break;
            }
        } catch (error) {
            console.error('Error processing message:', error);
        }
    });
    
    // Xử lý khi client ngắt kết nối
    ws.on('close', () => {
        removeFromAllNotes(ws);
        console.log('Client disconnected');
    });
});

// Xử lý khi user tham gia note
function handleJoinNote(ws, noteId) {
    // Xóa ws khỏi tất cả các note khác (nếu có)
    removeFromAllNotes(ws);
    
    // Thêm ws vào note mới
    if (!noteConnections.has(noteId)) {
        noteConnections.set(noteId, new Set());
    }
    noteConnections.get(noteId).add(ws);
    
    // Lưu noteId vào ws object để dễ dàng xóa sau này
    ws.noteId = noteId;
}

// Xử lý khi user cập nhật note
function handleNoteUpdate(ws, data) {
    if (!ws.noteId) return;
    
    const connections = noteConnections.get(ws.noteId);
    if (!connections) return;
    
    // Gửi cập nhật đến tất cả các user khác đang xem/chỉnh sửa note này
    const message = JSON.stringify({
        type: 'note_update',
        noteId: ws.noteId,
        changes: data.changes,
        userId: data.userId // để phân biệt người gửi
    });
    
    connections.forEach(client => {
        if (client !== ws && client.readyState === WebSocket.OPEN) {
            client.send(message);
        }
    });
}

// Xử lý vị trí con trỏ của user
function handleCursorPosition(ws, data) {
    if (!ws.noteId) return;
    
    const connections = noteConnections.get(ws.noteId);
    if (!connections) return;
    
    // Gửi vị trí con trỏ đến tất cả user khác
    const message = JSON.stringify({
        type: 'cursor_position',
        noteId: ws.noteId,
        position: data.position,
        userId: data.userId
    });
    
    connections.forEach(client => {
        if (client !== ws && client.readyState === WebSocket.OPEN) {
            client.send(message);
        }
    });
}

// Xóa ws khỏi tất cả các note
function removeFromAllNotes(ws) {
    if (ws.noteId && noteConnections.has(ws.noteId)) {
        noteConnections.get(ws.noteId).delete(ws);
        
        // Xóa Set nếu không còn kết nối nào
        if (noteConnections.get(ws.noteId).size === 0) {
            noteConnections.delete(ws.noteId);
        }
    }
}

console.log(`WebSocket server is running on port ${port}`); 