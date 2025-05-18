<?php
session_start();

require_once 'db.php'; // Kết nối CSDL

if (isset($_SESSION['user'])) {
    header("Location: home.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    if (!empty($email) && !empty($pass)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($pass, $user['password'])) {
                $_SESSION['user'] = [
                    'email' => $user['email'],
                    'name' => $user['display_name'],   
                    'avatar' => $user['avatar']
                ];
                header("Location: home.php");
                exit;
            } else {
                $error = "⚠️ Mật khẩu không đúng.";
            }
        } else {
            $error = "⚠️ Tài khoản không tồn tại.";
        }
    } else {
        $error = "⚠️ Vui lòng nhập đầy đủ email và mật khẩu.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
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

        /* Thêm màu hồng cho các phần tử khác */
        .form-control:focus {
            border-color: #f78da7;
            box-shadow: 0 0 0 0.2rem rgba(247, 141, 167, 0.25);
        }

        /* Màu nền trang */
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

        /* Thêm style cho card đăng nhập */
        .card {
            border: none;
            box-shadow: 0 4px 20px rgba(240, 84, 101, 0.1);
            border-radius: 15px;
        }

        .card-header {
            background: linear-gradient(135deg, #f78da7, #f05465);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }
    </style>
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-4">Đăng nhập</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
    <div class="mb-3">
        <input type="email" name="email" class="form-control" placeholder="Email..." required>
    </div>
    <div class="mb-3">
        <input type="password" name="password" class="form-control" placeholder="Mật khẩu..." required>
    </div>
    <p class="text-end">
        <a href="forgot_password.php" class="text-decoration-none">Quên mật khẩu?</a>
    </p>
    <button type="submit" name="login" class="btn btn-primary w-100">Đăng nhập</button>
</form>
        <p class="mt-3 text-center">
            Chưa có tài khoản? <a href="register.php">Đăng ký</a>
        </p>

    </div>
</body>
</html>
