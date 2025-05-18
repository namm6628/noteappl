const WebSocket = require('ws');
require('dotenv').config();

// Lắng nghe trên cổng mà Render cấp phát (cổng từ biến môi trường)
const port = process.env.PORT || 8080;  // Cổng mặc định nếu không có biến môi trường
const wss = new WebSocket.Server({ port });

// Khi server thực sự bắt đầu lắng nghe
wss.on('listening', () => {
  console.log(`WebSocket server is running on port ${port}`);
});

// Lưu trữ kết nối theo noteId
const noteConnections = new Map();

wss.on('connection', (ws) => {
    console.log('Client connected');
    
    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);
            switch (data.type) {
                case 'join_note':
                    handleJoinNote(ws, data.noteId);
                    break;
                case 'note_update':
                    handleNoteUpdate(ws, data);
                    break;
                case 'cursor_position':
                    handleCursorPosition(ws, data);
                    break;
            }
        } catch (error) {
            console.error('Error processing message:', error);
        }
    });
    
    ws.on('close', () => {
        removeFromAllNotes(ws);
        console.log('Client disconnected');
    });
});

function handleJoinNote(ws, noteId) {
    removeFromAllNotes(ws);
    if (!noteConnections.has(noteId)) {
        noteConnections.set(noteId, new Set());
    }
    noteConnections.get(noteId).add(ws);
    ws.noteId = noteId;
}

function handleNoteUpdate(ws, data) {
    if (!ws.noteId) return;
    const connections = noteConnections.get(ws.noteId);
    if (!connections) return;
    const message = JSON.stringify({
        type: 'note_update',
        noteId: ws.noteId,
        changes: data.changes,
        userId: data.userId
    });
    connections.forEach(client => {
        if (client !== ws && client.readyState === WebSocket.OPEN) {
            client.send(message);
        }
    });
}

function handleCursorPosition(ws, data) {
    if (!ws.noteId) return;
    const connections = noteConnections.get(ws.noteId);
    if (!connections) return;
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

function removeFromAllNotes(ws) {
    if (ws.noteId && noteConnections.has(ws.noteId)) {
        noteConnections.get(ws.noteId).delete(ws);
        if (noteConnections.get(ws.noteId).size === 0) {
            noteConnections.delete(ws.noteId);
        }
    }
}
