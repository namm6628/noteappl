# Note App WebSocket Server

Server WebSocket cho tính năng cộng tác thời gian thực trong Note App.

## Cài đặt

1. Cài đặt Node.js và npm (nếu chưa có)
2. Clone repository này
3. Chạy lệnh cài đặt dependencies:
```bash
npm install
```

## Cấu hình

1. Tạo file `.env` từ mẫu:
```
PORT=8080
ALLOWED_ORIGINS=http://localhost,http://127.0.0.1
```

2. Điều chỉnh các giá trị trong `.env` theo nhu cầu:
- `PORT`: Cổng chạy WebSocket server
- `ALLOWED_ORIGINS`: Danh sách các domain được phép kết nối, phân cách bằng dấu phẩy

## Chạy server

Development mode (với auto-reload):
```bash
npm run dev
```

Production mode:
```bash
npm start
```

## API WebSocket

Server hỗ trợ các loại tin nhắn sau:

### 1. Tham gia note
```javascript
{
    type: 'join_note',
    noteId: 'note_id'
}
```

### 2. Cập nhật note
```javascript
{
    type: 'note_update',
    noteId: 'note_id',
    changes: {
        title: 'New title',
        content: 'New content'
    },
    userId: 'user_email'
}
```

### 3. Vị trí con trỏ
```javascript
{
    type: 'cursor_position',
    noteId: 'note_id',
    position: {
        x: 100,
        y: 200
    },
    userId: 'user_email'
}
``` 