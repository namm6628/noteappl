const express = require('express');
const path = require('path');
const WebSocket = require('ws');
require('dotenv').config();

const app = express();
const port = process.env.PORT || 8080;

// Phục vụ file tĩnh trong thư mục public
app.use(express.static(path.join(__dirname, 'public')));

// Route mặc định trả về index.html
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Tạo HTTP server từ app express
const server = app.listen(port, () => {
  console.log(`HTTP server running on port ${port}`);
});

// Tạo WebSocket server trên cùng HTTP server
const wss = new WebSocket.Server({ server });

// --- Phần xử lý WebSocket của bạn ---
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
    userId: data.userId,
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
    userId: data.userId,
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
