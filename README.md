# WebSocket Test Application

Ứng dụng demo WebSocket đơn giản sử dụng Socket.IO và Express.

## Yêu cầu

- Node.js (phiên bản 12.0.0 trở lên)
- npm (Node Package Manager)

## Cài đặt

1. Clone repository này về máy của bạn
2. Mở terminal và di chuyển đến thư mục dự án
3. Cài đặt các dependencies:
   ```bash
   npm install
   ```

## Chạy ứng dụng

1. Khởi động server trong chế độ development (có auto-reload):
   ```bash
   npm run dev
   ```

   Hoặc khởi động server thông thường:
   ```bash
   npm start
   ```

2. Mở trình duyệt và truy cập: http://localhost:3000

## Tính năng

- Kết nối WebSocket realtime
- Hỗ trợ cả WebSocket và polling transport
- Tự động reconnect khi mất kết nối
- Hiển thị trạng thái kết nối
- Chat đơn giản giữa các client
- Xử lý lỗi và logging đầy đủ

## Cấu trúc thư mục

```
.
├── public/
│   └── index.html
├── server.js
├── package.json
└── README.md
```

## Xử lý sự cố

1. Nếu gặp lỗi "Port already in use":
   - Kiểm tra và đóng các ứng dụng đang sử dụng port 3000
   - Hoặc thay đổi port trong file server.js

2. Nếu không thể kết nối:
   - Kiểm tra console của trình duyệt và server để xem log lỗi
   - Đảm bảo firewall không chặn kết nối
   - Thử sử dụng transport polling nếu WebSocket không hoạt động

3. Nếu tin nhắn không được gửi:
   - Kiểm tra kết nối mạng
   - Xem log lỗi trong console
   - Đảm bảo server đang chạy 