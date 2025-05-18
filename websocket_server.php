<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use NoteApp\NoteServer;

// Tạo server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new NoteServer()
        )
    ),
    8080,
    '0.0.0.0' // Cho phép kết nối từ mọi interface
);

echo "WebSocket server starting...\n";

// Chạy server
$server->run(); 