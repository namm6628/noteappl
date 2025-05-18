<?php
session_start();
require_once 'db.php';
require_once 'send_verification.php';

if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$reg_error = null;

if (
    isset($_POST['register']) &&
    isset($_POST['reg_email']) &&
    isset($_POST['reg_password']) &&
    isset($_POST['reg_password2'])
) {
    $email = $_POST['reg_email'];
    $name = $_POST['reg_name'] ?? '';
    $pass1 = $_POST['reg_password'];
    $pass2 = $_POST['reg_password2'];

    if (strlen($pass1) < 6) {
        $reg_error = "⚠️ Mật khẩu phải có ít nhất 6 ký tự.";
    }
    elseif ($pass1 !== $pass2) {
        $reg_error = "Mật khẩu không khớp.";
    }
     else {
        $hash = password_hash($pass1, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(16));

        // Kiểm tra email đã tồn tại
        $check = $conn->prepare("SELECT * FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check_res = $check->get_result();

        if ($check_res->num_rows > 0) {
            $reg_error = "Email đã được đăng ký.";
        } else {
            // Lưu user
            $stmt = $conn->prepare("INSERT INTO users (email, display_name, password, verify_token, is_verified) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("ssss", $email, $name, $hash, $token);
            $stmt->execute();

            // Gửi email xác minh
            send_verification_email($email, $token);

            // Lưu session đăng nhập (chưa xác minh)
            $_SESSION['user'] = [
                'email' => $email,
                'display_name' => $name,
                'is_verified' => 0
            ];

            header("Location: index.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-primary {
            background: linear-gradient(135deg, #f78da7, #f05465) !important;
            border-color: #f05465 !important;
            box-shadow: 0 2px 6px rgba(240, 84, 101, 0.2);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #f05465, #e73d51) !important;
            box-shadow: 0 4px 12px rgba(240, 84, 101, 0.3);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 2px 4px rgba(240, 84, 101, 0.2);
        }

        .btn-link {
            color: #f05465 !important;
        }

        .btn-link:hover {
            color: #e73d51 !important;
            text-decoration: none;
        }

        .form-control:focus {
            border-color: #f78da7;
            box-shadow: 0 0 0 0.2rem rgba(247, 141, 167, 0.25);
        }

        body {
            background: linear-gradient(135deg, #ffeef0, #fff5f7, #fce4ec);
            background-size: 400% 400%;
            animation: gradientBG 30s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .card {
            border: none;
            box-shadow: 0 4px 20px rgba(240, 84, 101, 0.1);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:hover {
            background-color: #fff9fa;
        }

        a {
            color: #f05465;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        a:hover {
            color: #e73d51;
            text-decoration: underline;
        }

        .alert-danger {
            background-color: #fff0f3;
            border-color: #ffd7e0;
            color: #e73d51;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card p-4 shadow" style="width: 100%; max-width: 450px;">
        <h3 class="text-center mb-4" style="color: #f05465;">Đăng ký tài khoản</h3>
        <?php if ($reg_error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($reg_error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <input type="email" name="reg_email" class="form-control" placeholder="Email của bạn..." required>
            </div>
            <div class="mb-3">
                <input type="text" name="reg_name" class="form-control" placeholder="Tên hiển thị..." required>
            </div>
            <div class="mb-3">
                <input type="password" name="reg_password" class="form-control" placeholder="Mật khẩu..." required>
            </div>
            <div class="mb-3">
                <input type="password" name="reg_password2" class="form-control" placeholder="Xác nhận mật khẩu..." required>
            </div>
            <button type="submit" name="register" class="btn btn-primary w-100">Đăng ký</button>
        </form>
        <p class="mt-4 text-center">
            Đã có tài khoản? <a href="index.php">Đăng nhập ngay</a>
        </p>
    </div>
</body>
</html>
