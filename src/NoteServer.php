<?php

namespace NoteApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class NoteServer implements MessageComponentInterface
{
    protected $clients;
    protected $userConnections;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
        echo "WebSocket server started on port 8080\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $data = json_decode($msg, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format');
            }
            
            if ($data['type'] === 'register') {
                // Đăng ký user với connection
                $this->userConnections[$data['email']] = $from;
                echo "User {$data['email']} registered\n";
                
                // Gửi xác nhận kết nối thành công về client
                $from->send(json_encode([
                    'type' => 'connection_success',
                    'message' => 'Connected successfully'
                ]));
            }
            else if ($data['type'] === 'note_update') {
                // Gửi cập nhật đến những người được chia sẻ
                foreach ($data['note']['shared'] as $shared) {
                    if (isset($this->userConnections[$shared['email']])) {
                        $conn = $this->userConnections[$shared['email']];
                        $conn->send(json_encode([
                            'type' => 'note_updated',
                            'note' => $data['note']
                        ]));
                    }
                }
            }
        } catch (\Exception $e) {
            echo "Error processing message: {$e->getMessage()}\n";
            $from->send(json_encode([
                'type' => 'error',
                'message' => $e->getMessage()
            ]));
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        // Xóa user connection
        foreach ($this->userConnections as $email => $connection) {
            if ($connection === $conn) {
                echo "User {$email} disconnected\n";
                unset($this->userConnections[$email]);
                break;
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
} 