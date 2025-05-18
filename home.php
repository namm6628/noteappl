<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
if (!isset($_SESSION['user']['is_verified'])) {
    // Truy vấn từ database để lấy trạng thái xác minh
    require_once 'db.php';
    $email = $_SESSION['user']['email'];

    $stmt = $conn->prepare("SELECT is_verified FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $_SESSION['user']['is_verified'] = $user['is_verified'] ?? 0;

    $stmt->close();
}
?>
<?php if ($_SESSION['user']['is_verified'] == 0): ?>
    <div class="alert alert-warning text-center mb-0">
        <strong>Tài khoản của bạn chưa được xác minh.</strong> Vui lòng kiểm tra email để hoàn tất xác minh.
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Ghi chú</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
  --bg-color: #f9f9f9;
  --text-color: #333;
  --card-color: #fff;
  --border-color: #ddd;
  --primary-color: #3498db;
  --danger-color: #e74c3c;
  --hover-color: #2980b9;
  --dark-bg: #18191a;
  --dark-card: #242526;
  --dark-border: #3a3b3c;
  --dark-badge: #3a3b3c;
  --dark-text: #ecf0f1;
}

    /* Dark mode override */
    body.dark-mode {
      --bg-color: var(--dark-bg);
      --text-color: var(--dark-text);
      --card-color: var(--dark-card);
      --border-color: var(--dark-border);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html, body {
      height: 100%;
    }
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--bg-color);
      color: var(--border-color, card-color, text-color);
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Headings */
    h2 {
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 10px;
      color: #2c3e50;
    }

    /* Layout */
    .main {
      max-width: 900px;
      margin: 40px auto;
      padding: 40px;
      background-color: var(--card-color);
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    /* Textarea */
    textarea {
      width: 100%;
      height: 250px;
      padding: 20px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
      resize: vertical;
      background-color: var(--card-color);
      color: var(--text-color);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    textarea:focus {
      outline: none;
      border-color: var(--primary-color);
    }

    /* Controls */
    .controls {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
      gap: 30px;
      flex-wrap: wrap;
    }

    label {
      font-size: 16px;
      font-weight: 500;
      margin-right: 10px;
    }

    select,
    input[type="color"],
    .form-control,
    .form-select {
      padding: 8px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
      background-color: var(--card-color);
      color: var(--text-color);
      transition: background-color 0.3s, border-color 0.3s;
    }

    select:focus,
    input[type="color"]:focus,
    .form-control:focus,
    .form-select:focus {
      outline: none;
      border-color: var(--primary-color);
    }

    /* Notes */
    .note-container {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      padding: 1rem;
      transition: column-count 0.4s, grid-template-columns 0.4s, width 0.4s, all 0.4s;;
    }
    @media (max-width: 992px) { .note-container { column-count: 2; } }
    @media (max-width: 576px) { .note-container { column-count: 1; } }
    
    .note-item {
      break-inside: avoid;
      margin-bottom: 1rem;
      display: inline-block;
      width: 100%;
    }

    .note-item {
      margin: 0px;
    }

    .grid-view .note-item {
      width: calc(33.333% - 20px);
      margin: 0 0 10px 0;
    }

    .list-view .note-item {
      width: 100%;
    }

    @media (max-width: 992px) {
      .grid-view .note-item {
        width: calc(50% - 10px);
      }
    }
    @media (max-width: 576px) {
      .grid-view .note-item {
        width: 100%;
      }
    }

    .note-card {
      border: none;
      border-radius: 12px;
      background-color: var(--card-color);
      box-shadow: 0 1px 5px rgba(0,0,0,0.1);
      padding: 12px 16px;
      transition: box-shadow 0.22s cubic-bezier(.4,0,.2,1), transform 0.18s cubic-bezier(.4,0,.2,1);
    }

    .note-card:hover {
      box-shadow: 0 8px 24px rgba(60,64,67,.18);
      transform: translateY(-2px) scale(1.01);
    }

    /* Animations */
    .fade-in {
      opacity: 0;
      transform: translateY(10px);
      animation: fadeInUp 0.4s ease forwards;
    }

    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .toolbar-fade {
      opacity: 0;
      animation: toolbarFadeIn 0.5s forwards;
    }

    @keyframes toolbarFadeIn {
      to {
        opacity: 1;
      }
    }

    /* Buttons */
    .btn,
    .custom-btn {
      border-radius: 20px;
      padding: 6px 14px;
      font-size: 0.9rem;
      transition: all 0.2s ease-in-out;
      cursor: pointer;
    }

    .custom-btn {
      box-shadow: 0 2px 6px rgba(0, 123, 255, 0.2);
    }

    .custom-btn:hover {
      background-color: var(--primary-color);
      color: white;
      transform: scale(1.03);
      box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
    }

    .custom-btn:active {
      transform: scale(0.98);
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .note-actions .btn {
      padding: 6px 14px;
    }

    /* Profile/Logout */
    .header-buttons {
      position: absolute;
      top: 20px;
      right: 20px;
      display: flex;
      gap: 12px;
      z-index: 1000;
    }

    .logout-link,
    .profile-link {
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      color: white;
      background-color: var(--primary-color);
      transition: background-color 0.3s ease, transform 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    /* Nút đăng xuất màu đỏ */
    .logout-link {
      background-color: var(--danger-color);
    }

    .logout-link:hover {
      background-color: #c0392b;
      transform: scale(1.05);
    }

    /* Nút hồ sơ màu xanh */
    .profile-link {
      background-color: var(--primary-color);
    }

    .profile-link:hover {
      background-color: var(--hover-color);
      transform: scale(1.05);
    }

    /* List styling */
    ul {
      list-style: none;
      padding-left: 0;
    }

    ul li {
      font-size: 18px;
      margin-bottom: 10px;
    }

    /* Theme toggle */
    .theme-toggle-container {
      display: flex;
      justify-content: flex-start;
      gap: 15px;
      margin-top: 10px;
    }

    /* Google Keep-like grid layout */
    .note-container {
      column-count: 3;
      column-gap: 1rem;
      padding: 1rem;
    }

    @media (max-width: 992px) {
      .note-container {
        column-count: 2;
      }
    }
    @media (max-width: 576px) {
      .note-container {
        column-count: 1;
      }
    }

    .note-item {
      break-inside: avoid;
      margin-bottom: 1rem;
      display: inline-block;
      width: 100%;
    }

    .note-card {
      border: none;
      border-radius: 12px;
      background-color: var(--card-color);
      box-shadow: 0 1px 5px rgba(0,0,0,0.1);
      padding: 12px 16px;
      transition: box-shadow 0.22s cubic-bezier(.4,0,.2,1), transform 0.18s cubic-bezier(.4,0,.2,1);
    }
    .label-item .btn { transition: opacity 0.2s; }
        .label-item span:last-child { opacity: 0; pointer-events: none; }
        .label-item:hover span:last-child { opacity: 1; pointer-events: auto; }
        .label-item.active, .label-item.active span { background: var(--primary-color) !important; color: #fff !important; }
        .form-control, .form-select {
          padding: 8px;
          border-radius: 6px;
          border: 0px solid #ccc;
          font-size: 15px;
          background-color: var(--card-color);
          color: var(--text-color);
          transition: background-color 0.3s, border-color 0.3s;
        }

    .note-card:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transform: translateY(-2px);
    }

    /* xử lí nhãn */
    .note-menu-btn {
      background: #f8f9fa;
      border: none;
      color: #888;
      font-size: 1em;
      border-radius: 50%;
      width: 42px;
      height: 42px;
      min-width: 28px;
      min-height: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.15s, color 0.15s;
      cursor: pointer;
      margin-left: 0;
      margin-right: 0;
      box-shadow: 0 1px 4px rgba(60,64,67,.08);
      padding: 0;
    }
    .note-menu-btn:hover, .note-menu-btn:focus {
      background: #ececec;
      color: #222;
    }
    .note-menu-btn i {
      font-size: 1em;
    }
    .note-menu-dropdown {
      position: absolute;
      right: 10px;
      top: 40px;
      min-width: 220px;
      background: var(--card-color);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
      z-index: 100;
      display: none;
      padding: 10px 0;
    }
    .note-menu-dropdown label {
      display: flex;
      align-items: center;
      padding: 4px 16px;
      cursor: pointer;
      font-size: 15px;
    }
    .note-menu-dropdown label input[type="checkbox"] {
      margin-right: 8px;
    }
    .note-menu-dropdown button,
    .note-menu-dropdown input[type="file"] {
      width: 100%;
      margin: 4px 0;
    }
    .note-menu-dropdown .menu-action {
      background: none;
      border: none;
      color: #333;
      text-align: left;
      width: 100%;
      padding: 6px 16px;
      font-size: 15px;
      cursor: pointer;
    }
    .note-menu-dropdown .menu-action:hover {
      background: #f1f3f4;
    }

    .note-card .fa-thumbtack {
      transition: transform 0.2s;
    }
    .note-card .fa-thumbtack:hover {
      transform: rotate(-20deg) scale(1.2);
    }

    .header-actions .btn-action {
      background: #f5f6fa;
      border: none;
      border-radius: 50%;
      width: 44px;
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.35rem;
      color: #222;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      transition: background 0.18s, color 0.18s, box-shadow 0.18s;
      padding: 0;
    }
    .header-actions .btn-action:hover,
    .header-actions .btn-action:focus {
      background: #e1e7ef;
      color: #1976d2;
      box-shadow: 0 4px 16px rgba(0,0,0,0.10);
    }
    .header-actions .btn-action-danger {
      color: #e74c3c;
    }
    .header-actions .btn-action-danger:hover,
    .header-actions .btn-action-danger:focus {
      background: #fdecea;
      color: #c0392b;
    }
    #sidebar {
      transition: width 0.3s, min-width 0.3s, max-width 0.3s, padding 0.3s;
    }
    #sidebar.collapsed {
      width: 0 !important;
      min-width: 0 !important;
      max-width: 0 !important;
      padding: 0 !important;
      overflow: hidden;
    }
    @media (max-width: 992px) {
      #sidebar {
        position: absolute;
        z-index: 2000;
        background: #fff;
        box-shadow: 2px 0 8px rgba(0,0,0,0.08);
        height: 100vh;
        left: 0;
        top: 64px;
        transition: left 0.3s;
      }
      #sidebar.collapsed {
        left: -300px;
      }

      .note-card {
        border-radius: 12px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.10) !important;
        padding: 18px 18px 12px 18px !important;
        min-height: 180px;
        margin-bottom: 16px;
        transition: box-shadow 0.22s cubic-bezier(.4,0,.2,1), transform 0.18s cubic-bezier(.4,0,.2,1) !important;
      }
      .note-card input, .note-card textarea {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        resize: none;
      }
      .note-card input:focus, .note-card textarea:focus {
        outline: none !important;
      }
      .note-card .btn-light {
        background: #f1f3f4 !important;
        border: none !important;
        color: #333 !important;
        box-shadow: none !important;
      }
    }

    .note-toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
  margin-top: 2px;
}
.note-toolbar-btn {
  background: #f8f9fa;
  border: none;
  color: #222;
  font-size: 0.9em;
  border-radius: 50%;
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
  cursor: pointer;
  box-shadow: 0 1px 4px rgba(60,64,67,.08);
}
.note-toolbar-btn:hover {
  background: #ececec;
}

/* Áp dụng style cho contentInput */
.contentInput {
  font-size: 16px;
  font-weight: normal;
  font-style: normal;
  text-decoration: none;
}
body {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

  background: linear-gradient(135deg, #ffeef0, #fff5f7, #fce4ec, #fff5f7);
  background-size: 400% 400%;
  animation: gradientBG 30s ease infinite;

  color: var(--text-color);
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* Khi dark mode bật, tắt animation gradient nền và chuyển về nền tối như cũ */
body.dark-mode {
  animation: none !important;
  background: var(--dark-bg) !important;
  color: var(--dark-text) !important;
}

/* Giữ nguyên các biến màu cho dark mode */
body.dark-mode {
  --bg-color: var(--dark-bg);
  --text-color: var(--dark-text);
  --card-color: var(--dark-card);
  --border-color: var(--dark-border);
}

/* Ô thêm nhãn (sidebar input + button) luôn giữ nền trắng, không đổi theo dark mode */
#addLabelForm input,
#addLabelForm button {
  background-color: #fff !important;
  color: #000 !important;
  border-color: #ccc !important;
  box-shadow: none !important;
}

/* Nếu muốn, có thể chỉnh padding/margin để đẹp hơn */
#addLabelForm input {
  border-radius: 6px;
  padding: 6px 10px;
}

#addLabelForm button {
  border-radius: 6px;
  padding: 6px 12px;
  cursor: pointer;
}
/* Viền cho ô tìm kiếm */
#searchInput {
  border: 2px solid #f78da7; /* màu hồng đào nhẹ */
  border-radius: 25px;        /* bo tròn */
  padding-left: 1.25rem;      /* thêm khoảng cách trái để đẹp */
  transition: border-color 0.3s ease;
}

#searchInput:focus {
  border-color: #f05465;      /* đổi viền khi focus sang màu đậm hơn */
  outline: none;
  box-shadow: 0 0 8px rgba(240, 84, 101, 0.4);
}

/* Viền cho ô thêm nhãn */
#newLabelInput {
  border: 2px solid #f78da7; /* cùng màu hồng đào nhẹ */
  border-radius: 8px;
  padding: 6px 12px;
  transition: border-color 0.3s ease;
}

#newLabelInput:focus {
  border-color: #f05465;
  outline: none;
  box-shadow: 0 0 8px rgba(240, 84, 101, 0.4);
}
body.dark-mode .navbar {
  background-color: #2a2a2a !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5) !important;
  color: #eee !important;
}

body.dark-mode .navbar .navbar-brand,
body.dark-mode .navbar input,
body.dark-mode .navbar button,
body.dark-mode .navbar .btn {
  color: #eee !important;
  background-color: transparent !important;
  border-color: transparent !important;
}

body.dark-mode .navbar input {
  background-color: #383838 !important;
  border: 1px solid #555 !important;
  color: #eee !important;
}

body.dark-mode .navbar input::placeholder {
  color: #bbb !important;
}
body.dark-mode input.form-control.fw-bold,
body.dark-mode input.form-control.fw-bold[readonly] {
  background-color: #383838 !important;
  color: #eee !important;
  border: 1px solid #555 !important;
}

body.dark-mode input.form-control.fw-bold::placeholder {
  color: #bbb !important;
}
/* Sidebar chứa danh sách nhãn và ô thêm nhãn */
#sidebar {
  background-color: #fff; /* nền trắng bình thường */
  border-right: 1px solid #f7cfd8; /* viền hồng nhạt */
  padding: 1rem;
  border-radius: 0 20px 20px 0;
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

/* Khi dark mode bật */
body.dark-mode #sidebar {
  background-color: #2f2f2f; /* nền tối đậm hơn */
  border-right: 1px solid #f05465; /* viền hồng đào đậm */
  color: #eee;
}

/* Phần danh sách nhãn */
#labelSidebar .list-group-item {
  background: transparent;
  color: inherit;
  border: none;
  transition: background-color 0.3s ease;
}

/* Màu nền nhãn được chọn */
#labelSidebar .list-group-item.active {
  background-color: #f28b82; /* hồng nhạt */
  color: white;
  font-weight: 600;
}

/* Khi dark mode bật, chỉnh màu nhãn được chọn */
body.dark-mode #labelSidebar .list-group-item.active {
  background-color: #f05465; /* hồng đào đậm */
  color: white;
}

/* Phần ô thêm nhãn (input + button) vẫn giữ nền sáng như bạn đã yêu cầu */
#addLabelForm input,
#addLabelForm button {
  background-color: #fff !important;
  color: #000 !important;
  border-color: #f78da7 !important;
  box-shadow: 0 0 6px rgba(247, 141, 167, 0.4);
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  border-radius: 8px;
}

/* Input focus */
#addLabelForm input:focus {
  border-color: #f05465 !important;
  box-shadow: 0 0 12px rgba(240, 84, 101, 0.6) !important;
  outline: none;
}


  </style>
</head>
<body>
  
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-3" style="height:64px;">
    <div class="d-flex align-items-center">
      <button id="sidebarToggle" class="btn btn-link text-dark me-2" style="font-size:1.5rem;">
        <i class="fas fa-bars"></i>
      </button>
      
      <span class="navbar-brand fw-bold d-flex align-items-center" id="logoAllNotesBtn" style="cursor:pointer;">
          <img src="https://i.postimg.cc/k4TngxNZ/logo-transparent.png" width="130" class="me-3" alt="Keep">
        </span>
    </div>
    <form class="mx-auto w-50">
      <input id="searchInput" type="text" class="form-control rounded-pill px-4" placeholder="Tìm kiếm ghi chú..." style="background:#f1f3f4;">
    </form>
   <div class="d-flex align-items-center gap-2 header-actions">
    <button id="toggleDarkMode" class="btn btn-action" title="Chế độ tối"><i class="fas fa-moon"></i></button>
      <a href="profile.php" class="btn btn-action p-0" title="Hồ sơ" style="overflow:hidden;">
        <img src="<?php echo htmlspecialchars($_SESSION['user']['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user']['email'] ?? 'U')); ?>"
          alt="Avatar"
          style="width:36px;height:36px;object-fit:cover;border-radius:50%;display:block;">
        </a>
      <a href="logout.php" class="btn btn-action btn-action-danger" title="Đăng xuất"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <aside id="sidebar" class="col-12 col-md-3 col-lg-2 pt-4" style="min-height:90vh;">
        <ul class="list-group list-group-flush" id="labelSidebar">
          <li id="allNotesBtn" class="list-group-item bg-warning bg-opacity-25 border-0 rounded-pill mb-1 fw-bold" style="cursor:pointer;">
              <i class="fa fa-lightbulb me-2"></i>Ghi chú
            </li>
        </ul>
        <div class="p-3">
          <form id="addLabelForm" class="d-flex align-items-center">
            <input type="text" id="newLabelInput" class="form-control form-control-sm" placeholder="Thêm nhãn..." required>
            <button type="submit" class="btn btn-sm btn-link text-primary ms-1"><i class="fa fa-plus"></i></button>
          </form>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="col-12 col-md-9 col-lg-10 py-4">
        <!-- Controls -->
        <div class="mb-3 d-flex gap-2 align-items-center">
          <button id="gridBtn" class="btn btn-outline-primary btn-sm custom-btn active"><i class="fas fa-th"></i> Lưới </button>
          <button id="listBtn" class="btn btn-outline-primary btn-sm custom-btn"><i class="fas fa-list"></i> Cột </button>
          <button id="addNoteBtn" class="btn btn-success btn-sm">+ Thêm ghi chú</button>
          <button id="refreshNotesBtn" class="btn btn-outline-secondary btn-sm"><i class="fas fa-sync"></i> Làm mới giao diện</button>
        </div>

        <!-- Notes Masonry Grid -->
        <div id="noteContainer" class="note-container grid-view" style="min-height:300px;">
          <!-- Notes will be rendered here -->
        </div>
      </main>
    </div>
  </div>
    <!-- Share note -->


  <script>

    const noteContainer = document.getElementById('noteContainer');
    const gridBtn = document.getElementById('gridBtn');
    const listBtn = document.getElementById('listBtn');
    const addNoteBtn = document.getElementById('addNoteBtn');
    const toggleDarkModeBtn = document.getElementById('toggleDarkMode');
    const searchInput = document.getElementById('searchInput');

    const userEmail = "<?php echo $_SESSION['user']['email']; ?>";
    const notesKey = 'notes_' + userEmail;
    const labelsKey = 'labels_' + userEmail;

    let notes = JSON.parse(localStorage.getItem(notesKey)) || [];
    let labels = JSON.parse(localStorage.getItem(labelsKey)) || [];

    function saveNotes() {
      localStorage.setItem(notesKey, JSON.stringify(notes));
      notes.forEach(note => {
        if (note.shared && note.shared.length > 0) syncSharedNote(note);
      });
    }
    function deleteNote(noteId, isSharedNote) {
      if (isSharedNote) {
        // 1. Xóa khỏi localStorage của người nhận
        let myNotes = JSON.parse(localStorage.getItem(notesKey)) || [];
        myNotes = myNotes.filter(n => n.id !== noteId);
        localStorage.setItem(notesKey, JSON.stringify(myNotes));

        // 2. Tìm và cập nhật trong notes của chủ sở hữu
        for (let i = 0; i < localStorage.length; i++) {
          const key = localStorage.key(i);
          if (key.startsWith('notes_') && key !== notesKey) {
            let ownerNotes = JSON.parse(localStorage.getItem(key)) || [];
            const noteIndex = ownerNotes.findIndex(n => n.id === noteId);
            if (noteIndex !== -1 && ownerNotes[noteIndex].shared) {
              // Xóa email người nhận khỏi danh sách shared
              ownerNotes[noteIndex].shared = ownerNotes[noteIndex].shared.filter(s => s.email !== userEmail);
              localStorage.setItem(key, JSON.stringify(ownerNotes));
              break;
            }
          }
        }

        // 3. Cập nhật mảng notes và render lại
        notes = myNotes;
        renderNotes();
        return;
      }

      // Xử lý xóa ghi chú của chủ sở hữu
      const idx = notes.findIndex(n => n.id === noteId);
      if (idx === -1) return;

      const note = notes[idx];
      if (note.shared && note.shared.length > 0) {
        // Xóa ghi chú khỏi localStorage của tất cả người được chia sẻ
        note.shared.forEach(item => {
          const receiverKey = 'notes_' + item.email;
          let receiverNotes = JSON.parse(localStorage.getItem(receiverKey)) || [];
          receiverNotes = receiverNotes.filter(n => n.id !== noteId);
          localStorage.setItem(receiverKey, JSON.stringify(receiverNotes));
        });
      }

      // Xóa ghi chú khỏi mảng notes và lưu lại
      notes.splice(idx, 1);
      saveNotes();
      renderNotes();
    }
    function saveLabels() {
      localStorage.setItem(labelsKey, JSON.stringify(labels));
    }

    let currentTag = '';
    let searchQuery = '';
    function getAllTags() {
      const tags = new Set();
      notes.forEach(note => {
        (note.tags || []).forEach(tag => tags.add(tag));
      });
      labels.forEach(tag => tags.add(tag));
      return Array.from(tags);
    }
    function updateTagFilterOptions() {
      const allTags = getAllTags();
      tagFilter.innerHTML = '<option value="">Tất cả nhãn</option>';
      allTags.forEach(tag => {
        const opt = document.createElement('option');
        opt.value = tag;
        opt.textContent = tag;
        tagFilter.appendChild(opt);
      });
    }

    function getAllSharedNotes() {
      let allNotes = [];
      // Duyệt qua tất cả các key notes_* trong localStorage
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key.startsWith('notes_') && key !== notesKey) {
          const userNotes = JSON.parse(localStorage.getItem(key)) || [];
          userNotes.forEach(note => {
            if ((note.shared || []).some(s => s.email === userEmail)) {
              // Tránh trùng id với note của chính mình
              if (!notes.some(n => n.id === note.id)) {
                allNotes.push(note);
              }
            }
          });
        }
      }
      return allNotes;
    }

    // Google Keep-like label management
    function renderLabels() {
      const labelSidebar = document.getElementById('labelSidebar');
       while (labelSidebar.children.length > 1) labelSidebar.removeChild(labelSidebar.lastChild);

      labels.forEach((label, idx) => {
        const li = document.createElement('li');
        li.className = 'list-group-item border-0 d-flex align-items-center justify-content-between ps-4 label-item';
        if (currentTag === label) li.classList.add('active');
        li.style.cursor = 'pointer';

        // Label name
        const labelSpan = document.createElement('span');
        labelSpan.innerHTML = `<i class="fa fa-tag me-2"></i>${label}`;
        labelSpan.onclick = () => {
          currentTag = label;
          renderLabels();
          renderNotes();
          tagFilter.value = label;
        };

        // Actions (edit/delete)
        const actions = document.createElement('span');
        actions.style.display = 'none';
        actions.innerHTML = `
          <button class="btn btn-sm btn-link text-warning px-1" title="Đổi tên" tabindex="-1">✏️</button>
          <button class="btn btn-sm btn-link text-danger px-1" title="Xóa" tabindex="-1">🗑️</button>
        `;
        // Edit
        actions.children[0].onclick = (e) => {
          e.stopPropagation();
          const newName = prompt('Nhập tên mới cho nhãn:', label);
          if (newName && newName !== label && !labels.includes(newName)) {
            labels[idx] = newName;
            saveLabels();
            notes.forEach(note => {
              if (note.tags) note.tags = note.tags.map(t => t === label ? newName : t);
            });
            saveNotes();
            renderLabels();
            renderNotes();
          }
        };
        // Delete
        actions.children[1].onclick = (e) => {
          e.stopPropagation();
          if (confirm('Xóa nhãn này? Tất cả ghi chú sẽ mất nhãn này!')) {
            labels.splice(idx, 1);
            saveLabels();
            // Remove this label from all notes' tags
            notes.forEach(note => {
              if (note.tags) note.tags = note.tags.filter(t => t !== label);
            });
            saveNotes();
            renderLabels();
            renderNotes();
            if (currentTag === label) {
              currentTag = '';
            }
          }
        };
        // Show actions on hover (like Keep)
        li.onmouseenter = () => { actions.style.display = 'inline-block'; };
        li.onmouseleave = () => { actions.style.display = 'none'; };

        li.appendChild(labelSpan);
        li.appendChild(actions);
        labelSidebar.appendChild(li);
      });
    }
    // Add label form
    document.getElementById('addLabelForm').onsubmit = function(e) {
      e.preventDefault();
      const val = document.getElementById('newLabelInput').value.trim();
      if (val && !labels.includes(val)) {
        labels.push(val);
        saveLabels();
        renderLabels();
        renderNotes(); // <-- Add this line
        document.getElementById('newLabelInput').value = '';
      }
    };

    function createNoteElement(note, index, isSharedNote) {

      const isOwner = note.owner === userEmail;
      const sharedItem = (note.shared || []).find(s => s.email === userEmail);
      const canEdit = isOwner || (sharedItem && sharedItem.permission === 'edit');
      
      const div = document.createElement('div');
      div.className = 'note-item fade-in';
      div.style.transitionDelay = (index * 30) + 'ms';

      // Tạo card với class Keep-style
      const card = document.createElement('div');
      card.className = 'note-card keep-style';
      card.style.position = 'relative';
      card.style.background = note.color || '#fff';
      card.style.fontSize = note.fontSize || '16px';
      

      // Thêm màu nền và font chữ
      card.style.background = note.color || '#fff';
      card.style.minHeight = '180px';
      card.style.borderRadius = '12px';
      card.style.boxShadow = '0 2px 8px rgba(0,0,0,0.10)';
      card.style.padding = '18px 18px 12px 18px';
      card.style.transition = 'box-shadow 0.22s cubic-bezier(.4,0,.2,1), transform 0.2s cubic-bezier(.4,0,.2,1)';



      

      // Add prominent pin icon if pinned
      if (note.pinned) {
        const pinIcon = document.createElement('span');
        pinIcon.innerHTML = '<i class="fas fa-thumbtack"></i>';
        pinIcon.style.position = 'absolute';
        pinIcon.style.top = '6px';
        pinIcon.style.right = '12px';
        pinIcon.style.fontSize = '1.6rem';
        pinIcon.style.color = '#f7b731';
        pinIcon.style.transform = 'rotate(45deg)';
        pinIcon.style.transition = 'transform 0.2s ease';
        pinIcon.title = 'Đã ghim';
        pinIcon.style.cursor = 'pointer';
        pinIcon.onclick = (e) => {
          e.stopPropagation();
          notes[index].pinned = false;
          notes[index].updatedAt = Date.now();
          saveNotes();
          renderNotes();
        };
        pinIcon.onmouseover = () => {
          pinIcon.style.transform = 'rotate(45deg) scale(1.2)';
        };
        pinIcon.onmouseout = () => {
          pinIcon.style.transform = 'rotate(45deg)';
        };
        card.appendChild(pinIcon);
      }

      // If locked and not unlocked, show lock overlay and return
      if (!isSharedNote && note.locked && !note._unlocked) {
      const lockOverlay = document.createElement('div');
      lockOverlay.className = 'lock-overlay';
      lockOverlay.innerHTML = `
        <div class="lock-content">
          <div class="lock-icon">
            <i class="fas fa-lock "></i>
          </div>
          <div class="lock-title">Ghi chú được bảo vệ</div>
          <div class="lock-input-group">
            <input type="password" class="lock-input" placeholder="Nhập mật khẩu">
            <button class="unlock-btn">
              <i class="fas fa-unlock"></i>
              <span>Mở khóa</span>
            </button>
          </div>
          <div class="lock-error"></div>
        </div>
      `;

      // Add styles for the lock overlay
      const style = document.createElement('style');
      style.textContent = `
        .lock-overlay {
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: rgba(255, 255, 255, 0.95);
          backdrop-filter: blur(8px);
          display: flex;
          align-items: center;
          justify-content: center;
          z-index: 10;
          border-radius: 12px;
          transition: all 0.3s ease;
        }

        .dark-mode .lock-overlay {
          background: rgba(36, 37, 38, 0.95);
        }

        .lock-content {
          text-align: center;
          padding: 20px;
          transform: translateY(0);
          transition: transform 0.3s ease;
        }

        .lock-icon {
          font-size: 2.5em;
          color: #f05465;
          margin-bottom: 15px;
          animation: lockBounce 1s ease infinite;
        }

        @keyframes lockBounce {
          0%, 100% { transform: translateY(0); }
          50% { transform: translateY(-5px); }
        }

        .lock-title {
          font-size: 1.2em;
          font-weight: 600;
          color: #333;
          margin-bottom: 20px;
        }

        .dark-mode .lock-title {
          color: #fff;
        }

        .lock-input-group {
          display: flex;
          flex-direction: column;
          gap: 10px;
          align-items: center;
        }

        .lock-input {
          width: 220px;
          padding: 10px 15px;
          border: 2px solid #f78da7;
          border-radius: 25px;
          font-size: 1em;
          outline: none;
          transition: all 0.3s ease;
          text-align: center;
        }

        .lock-input:focus {
          border-color: #f05465;
          box-shadow: 0 0 0 3px rgba(240, 84, 101, 0.2);
        }

        .unlock-btn {
          background: #f05465;
          color: white;
          border: none;
          padding: 10px 25px;
          border-radius: 25px;
          font-size: 0.6em;
          cursor: pointer;
          transition: all 0.3s ease;
          display: flex;
          align-items: center;
          gap: 8px;
        }

        .unlock-btn:hover {
          background: #e63946;
          transform: translateY(-2px);
          box-shadow: 0 4px 12px rgba(240, 84, 101, 0.3);
        }

        .unlock-btn:active {
          transform: translateY(0);
        }

        .lock-error {
          color: #e63946;
          margin-top: 10px;
          font-size: 0.9em;
          min-height: 20px;
          opacity: 0;
          transition: opacity 0.3s ease;
        }

        .lock-error.show {
          opacity: 1;
        }

        .unlock-success {
          animation: unlockFadeOut 0.5s ease forwards;
        }

        @keyframes unlockFadeOut {
          to {
            opacity: 0;
            transform: scale(1.1);
          }
        }
      `;
      document.head.appendChild(style);

      const pwdInput = lockOverlay.querySelector('.lock-input');
      const unlockBtn = lockOverlay.querySelector('.unlock-btn');
      const errorDiv = lockOverlay.querySelector('.lock-error');

      const showError = (message) => {
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
        pwdInput.classList.add('error');
        setTimeout(() => {
          errorDiv.classList.remove('show');
          pwdInput.classList.remove('error');
        }, 3000);
      };

      const unlockNote = () => {
        if (btoa(pwdInput.value) === note.password) {
          lockOverlay.classList.add('unlock-success');
          setTimeout(() => {
            note._unlocked = true;
            if (!isSharedNote) {
              notes[index]._unlocked = true;
              saveNotes();
            } else {
              let myNotes = JSON.parse(localStorage.getItem(notesKey)) || [];
              const myIdx = myNotes.findIndex(n => n.id === note.id);
              if (myIdx !== -1) {
                myNotes[myIdx]._unlocked = true;
                localStorage.setItem(notesKey, JSON.stringify(myNotes));
              }
            }
            renderNotes();
          }, 500);
        } else {
          showError('Mật khẩu không đúng!');
          pwdInput.value = '';
          pwdInput.focus();
        }
      };

      unlockBtn.onclick = unlockNote;
      pwdInput.onkeydown = (e) => {
        if (e.key === 'Enter') unlockNote();
      };

      card.appendChild(lockOverlay);
      div.appendChild(card);
      return div;
      }

    // Title input
    const titleInput = document.createElement('input');
    titleInput.className = 'form-control mb-2 fw-bold';
    titleInput.placeholder = 'Tiêu đề...';
    titleInput.value = note.title;
    titleInput.readOnly = !canEdit;

  // Label badges
  const labelList = document.createElement('div');
  (note.tags || []).forEach(tag => {
    const span = document.createElement('span');
    span.className = 'badge bg-primary me-1';
    span.textContent = tag;
    labelList.appendChild(span);
  });

  // Content input
  const contentInput = document.createElement('textarea');
  contentInput.className = 'form-control  mb-2';
  contentInput.placeholder = 'Nội dung ghi chú...';
  contentInput.rows = 4;
  contentInput.value = note.content;

  titleInput.readOnly = !canEdit;
  contentInput.readOnly = !canEdit;
  titleInput.style.backgroundColor = canEdit ? "" : "#f5f5f5";
  contentInput.style.backgroundColor = canEdit ? "" : "#f5f5f5";

  // === Tạo toolbar chứa các nút ===
const noteToolbar = document.createElement('div');
noteToolbar.className = 'note-toolbar mt-2 mb-2 d-flex align-items-center gap-2';

// ==== Nút đổi màu nền ====
const colorBtn = document.createElement('button');
colorBtn.type = 'button';
colorBtn.className = 'note-toolbar-btn';
colorBtn.title = 'Đổi màu nền';
colorBtn.innerHTML = '<i class="fas fa-palette"></i>';
noteToolbar.appendChild(colorBtn);

// Bảng chọn màu nền
const colorPalette = document.createElement('div');
colorPalette.style.position = 'absolute';
colorPalette.style.bottom = '50px';
colorPalette.style.left = '10px';
colorPalette.style.background = '#fff';
colorPalette.style.border = '1px solid #eee';
colorPalette.style.borderRadius = '12px';
colorPalette.style.boxShadow = '0 2px 8px rgba(0,0,0,0.12)';
colorPalette.style.padding = '10px 12px 6px 12px';
colorPalette.style.display = 'none';
colorPalette.style.zIndex = 1000;
colorPalette.style.minWidth = '220px';

// Các màu nền (giống Google Keep)
const colors = [
  { color: "#fff", label: "Mặc định" },
  { color: "#f28b82" }, // Đỏ nhạt
  { color: "#fbbc04" }, // Vàng đậm
  { color: "#fff475" }, // Vàng nhạt
  { color: "#ccff90" }, // Xanh lá nhạt
  { color: "#a7ffeb" }, // Xanh ngọc nhạt
  { color: "#cbf0f8" }, // Xanh dương nhạt
  { color: "#aecbfa" }, // Xanh dương đậm
  { color: "#d7aefb" }, // Tím
  { color: "#fdcfe8" }, // Hồng
  { color: "#e6c9a8" }, // Nâu
  { color: "#e8eaed" }  // Xám
];
const colorRow = document.createElement('div');
colorRow.style.display = 'flex';
colorRow.style.gap = '8px';
colorRow.style.flexWrap = 'wrap';
colorRow.style.padding = '8px';

colors.forEach(cObj => {
  const colorRect = document.createElement('div');
  colorRect.style.width = '30px';  // Chiều rộng 30px
  colorRect.style.height = '20px'; // Chiều cao 20px (tỷ lệ 3:2)
  colorRect.style.background = cObj.color;
  colorRect.style.border = cObj.color === '#fff' ? '1px solid #e0e0e0' : 'none';
  colorRect.style.borderRadius = '4px';
  colorRect.style.cursor = 'pointer';
  colorRect.style.transition = 'transform 0.2s ease';

  if (cObj.label) {
    colorRect.title = cObj.label;
    // Thêm icon cho màu mặc định
    const icon = document.createElement('i');
    icon.className = 'fas fa-ban';
    icon.style.color = '#666';
    icon.style.fontSize = '12px'; // Tăng kích thước icon một chút
    icon.style.position = 'absolute';
    icon.style.left = '50%';
    icon.style.top = '50%';
    icon.style.transform = 'translate(-50%, -50%)';
    colorRect.style.position = 'relative';
    colorRect.appendChild(icon);
  }

  // Hiệu ứng hover
  colorRect.onmouseover = () => {
    colorRect.style.transform = 'scale(1.1)';
    colorRect.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
  };
  colorRect.onmouseout = () => {
    colorRect.style.transform = 'scale(1)';
    colorRect.style.boxShadow = 'none';
  };

  colorRect.onclick = (e) => {
    e.stopPropagation();
    note.color = cObj.color;
    if (!isSharedNote) {
      notes[index].color = cObj.color;
      saveNotes();
    } else {
      let myNotes = JSON.parse(localStorage.getItem(notesKey)) || [];
      const myIdx = myNotes.findIndex(n => n.id === note.id);
      if (myIdx !== -1) {
        myNotes[myIdx].color = cObj.color;
        localStorage.setItem(notesKey, JSON.stringify(myNotes));
      }
    }
    colorPalette.style.display = 'none';
    renderNotes();
  };
  colorRow.appendChild(colorRect);
});

// Cập nhật style cho color palette
colorPalette.style.position = 'absolute';
colorPalette.style.bottom = '50px';
colorPalette.style.left = '10px';
colorPalette.style.background = '#fff';
colorPalette.style.border = '1px solid #e0e0e0';
colorPalette.style.borderRadius = '8px';
colorPalette.style.boxShadow = '0 2px 8px rgba(0,0,0,0.12)';
colorPalette.style.padding = '4px';
colorPalette.style.display = 'none';
colorPalette.style.zIndex = 1000;
colorPalette.style.minWidth = '180px';

colorPalette.innerHTML = '';
colorPalette.appendChild(colorRow);

noteToolbar.appendChild(colorPalette);

colorBtn.onclick = (e) => {
  e.stopPropagation();
  colorPalette.style.display = colorPalette.style.display === 'none' ? 'block' : 'none';
};
// Ẩn bảng màu khi click ngoài
document.addEventListener('click', function hideColorPalette(e) {
  if (!colorPalette.contains(e.target) && e.target !== colorBtn) {
    colorPalette.style.display = 'none';
  }
});

// ==== Nút chỉnh cỡ/ký tự ====
const fontBtn = document.createElement('button');
fontBtn.type = 'button';
fontBtn.className = 'note-toolbar-btn';
fontBtn.title = 'Định dạng chữ';
fontBtn.innerHTML = '<i class="fas fa-text-height"></i>';
noteToolbar.appendChild(fontBtn);

// Popup chọn cỡ và kiểu chữ
const fontPopup = document.createElement('div');
fontPopup.style.position = 'absolute';
fontPopup.style.bottom = '50px';
fontPopup.style.left = '60px';
fontPopup.style.background = '#fff';
fontPopup.style.border = '1px solid #eee';
fontPopup.style.borderRadius = '12px';
fontPopup.style.boxShadow = '0 2px 8px rgba(0,0,0,0.12)';
fontPopup.style.padding = '10px 12px 6px 12px';
fontPopup.style.display = 'none';
fontPopup.style.zIndex = 1000;
fontPopup.style.minWidth = '180px';

// Cỡ chữ
const sizeLabel = document.createElement('div');
sizeLabel.textContent = 'Cỡ chữ:';
sizeLabel.style.fontWeight = 'bold';
sizeLabel.style.marginBottom = '6px';
fontPopup.appendChild(sizeLabel);

const sizes = [
  {label: 'Nhỏ', value: '14px'},
  {label: 'Vừa', value: '16px'},
  {label: 'Lớn', value: '20px'}
];
const sizeRow = document.createElement('div');
sizeRow.style.display = 'flex';
sizeRow.style.gap = '10px';
sizes.forEach(s => {
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.textContent = s.label;
  btn.style.fontSize = s.value;
  btn.style.padding = '4px 12px';
  btn.style.borderRadius = '8px';
  btn.style.border = '1px solid #eee';
  btn.style.background = note.fontSize === s.value ? '#e3f2fd' : '#fff';
  btn.onclick = (e) => {
    e.stopPropagation();
    note.fontSize = s.value;
    if (!isSharedNote) {
      notes[index].fontSize = s.value;
      saveNotes();
    } else {
      let myNotes = JSON.parse(localStorage.getItem(notesKey)) || [];
      const myIdx = myNotes.findIndex(n => n.id === note.id);
      if (myIdx !== -1) {
        myNotes[myIdx].fontSize = s.value;
        localStorage.setItem(notesKey, JSON.stringify(myNotes));
      }
    }
    fontPopup.style.display = 'none';
    renderNotes();
  };
  sizeRow.appendChild(btn);
});
fontPopup.appendChild(sizeRow);

// Kiểu chữ: Bold, Italic, Underline
const styleLabel = document.createElement('div');
styleLabel.textContent = 'Kiểu chữ:';
styleLabel.style.fontWeight = 'bold';
styleLabel.style.margin = '10px 0 6px 0';
fontPopup.appendChild(styleLabel);

const styleRow = document.createElement('div');
styleRow.style.display = 'flex';
styleRow.style.gap = '10px';

const styleBtns = [
  {icon: '<b>B</b>', style: 'bold', title: 'Tô đậm'},
  {icon: '<i>I</i>', style: 'italic', title: 'In nghiêng'},
  {icon: '<u>U</u>', style: 'underline', title: 'Gạch dưới'}
];
styleBtns.forEach(s => {
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.innerHTML = s.icon;
  btn.title = s.title;
  btn.style.fontSize = '1.1em';
  btn.style.padding = '4px 10px';
  btn.style.borderRadius = '8px';
  btn.style.border = '1px solid #eee';
  btn.style.background = note[s.style] ? '#e3f2fd' : '#fff';
  btn.onclick = (e) => {
    e.stopPropagation();
    note[s.style] = !note[s.style];
    if (!isSharedNote) {
      notes[index][s.style] = note[s.style];
      saveNotes();
    } else {
      let myNotes = JSON.parse(localStorage.getItem(notesKey)) || [];
      const myIdx = myNotes.findIndex(n => n.id === note.id);
      if (myIdx !== -1) {
        myNotes[myIdx][s.style] = note[s.style];
        localStorage.setItem(notesKey, JSON.stringify(myNotes));
      }
    }
    fontPopup.style.display = 'none';
    renderNotes();
  };
  styleRow.appendChild(btn);
});
fontPopup.appendChild(styleRow);
noteToolbar.appendChild(fontPopup);

fontBtn.onclick = (e) => {
  e.stopPropagation();
  fontPopup.style.display = fontPopup.style.display === 'none' ? 'block' : 'none';
};
// Ẩn popup khi click ngoài
document.addEventListener('click', function hideFontPopup(e) {
  if (!fontPopup.contains(e.target) && e.target !== fontBtn) {
    fontPopup.style.display = 'none';
  }
});

    // Update note
  // Áp dụng style cho contentInput
  contentInput.style.fontSize = note.fontSize || '16px';
  contentInput.style.fontWeight = note.bold ? 'bold' : 'normal';
  contentInput.style.fontStyle = note.italic ? 'italic' : 'normal';
  contentInput.style.textDecoration = note.underline ? 'underline' : 'none';

  // Sự kiện cho tiêu đề
    function saveTitle() {
      if (!canEdit) return;
      if (notes[index].title !== titleInput.value) {
        notes[index].title = titleInput.value;
        notes[index].updatedAt = Date.now();
        saveNotes();
        // Nếu là người nhận và có quyền chỉnh sửa, đồng bộ về cho chủ sở hữu
        if (!isOwner && sharedItem && sharedItem.permission === 'edit') {
          syncBackToOwner(notes[index]);
        }
        renderNotes();
      }
    }
    titleInput.addEventListener('blur', saveTitle);
    titleInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        titleInput.blur();
      }
    });

    const updatedAtDiv = document.createElement('div');
    updatedAtDiv.className = 'text-muted mt-1';
    updatedAtDiv.style.fontSize = '0.8em';
    updatedAtDiv.style.textAlign = 'left';
    const d = new Date(note.updatedAt);
    updatedAtDiv.textContent = 'Cập nhật: ' +
      d.toLocaleDateString('vi-VN') + ' ' +
      d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

    card.appendChild(updatedAtDiv);

    // Sự kiện cho nội dung
    function saveContent() {
      if (!canEdit) return;
      if (notes[index].content !== contentInput.value) {
        notes[index].content = contentInput.value;
        notes[index].updatedAt = Date.now();
        saveNotes();
        if (!isOwner && sharedItem && sharedItem.permission === 'edit') {
          syncBackToOwner(notes[index]);
        }
        renderNotes();
      }
    }
    contentInput.addEventListener('blur', saveContent);
    contentInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        contentInput.blur();
      }
    });


  // 3-dots menu button
  const menuBtn = document.createElement('button');
  menuBtn.className = 'note-menu-btn';
  menuBtn.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
  menuBtn.type = 'button';

  // Dropdown menu
  const menuDropdown = document.createElement('div');
  menuDropdown.className = 'note-menu-dropdown';
  menuDropdown.style.display = 'none';
  menuDropdown.style.position = 'absolute';
  menuDropdown.style.top = '-300px'; // Position above the button
  menuDropdown.style.right = '0';
  menuDropdown.style.background = '#fff';
  menuDropdown.style.borderRadius = '8px';
  menuDropdown.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
  menuDropdown.style.zIndex = '9999';
  menuDropdown.style.minWidth = '200px';
  menuDropdown.style.border = '1px solid #e0e0e0';

  // Show/hide menu
  menuBtn.onclick = (e) => {
    e.stopPropagation();
    const allDropdowns = document.querySelectorAll('.note-menu-dropdown');
    allDropdowns.forEach(d => {
      if (d !== menuDropdown) d.style.display = 'none';
    });
    menuDropdown.style.display = menuDropdown.style.display === 'none' ? 'block' : 'none';
  };

  // Close when clicking outside
  document.addEventListener('click', (e) => {
    if (!menuDropdown.contains(e.target) && e.target !== menuBtn) {
      menuDropdown.style.display = 'none';
    }
  });

  // Pin/Unpin
  const pinAction = document.createElement('button');
  pinAction.className = 'menu-action';
  pinAction.textContent = note.pinned ? 'Bỏ ghim' : 'Ghim';
  pinAction.onclick = () => {
    notes[index].pinned = !note.pinned;
    notes[index].updatedAt = Date.now();
    saveNotes();
    renderNotes();
    menuDropdown.style.display = 'none';
  };

  //share note

  const shareAction = document.createElement('button');
  shareAction.className = 'menu-action';
  shareAction.textContent = 'Chia sẻ ghi chú';
  shareAction.onclick = () => {
    // Lưu noteId vào localStorage/sessionStorage để chuyển sang trang chia sẻ
    openSharePopup(note, index);
    menuDropdown.style.display = 'none';
  };
  menuDropdown.appendChild(shareAction);

  menuDropdown.appendChild(pinAction);

  // Password protection
  const lockAction = document.createElement('button');
  lockAction.className = 'menu-action';
  lockAction.textContent = note.locked ? 'Đổi mật khẩu' : 'Khóa ghi chú';
  lockAction.onclick = () => {
    let pwd = prompt(note.locked ? 'Nhập mật khẩu mới cho ghi chú này:' : 'Đặt mật khẩu cho ghi chú này:');
    if (pwd && pwd.length >= 3) {
      notes[index].locked = true;
      notes[index].password = btoa(pwd);
      notes[index].updatedAt = Date.now();
      // Remove _unlocked flag if changing password
      delete notes[index]._unlocked;
      saveNotes();
      renderNotes();
      alert('Đã đặt mật khẩu cho ghi chú.');
    } else if (pwd !== null) {
      alert('Mật khẩu phải có ít nhất 3 ký tự.');
    }
    menuDropdown.style.display = 'none';
  };
  menuDropdown.appendChild(lockAction);

  if (note.locked) {
    const unlockAction = document.createElement('button');
    unlockAction.className = 'menu-action text-danger';
    unlockAction.textContent = 'Bỏ bảo vệ mật khẩu';
    unlockAction.onclick = () => {
      let pwd = prompt('Nhập mật khẩu hiện tại để bỏ bảo vệ:');
      if (pwd && btoa(pwd) === notes[index].password) {
        notes[index].locked = false;
        notes[index].password = '';
        notes[index].updatedAt = Date.now();
        delete notes[index]._unlocked;
        saveNotes();
        renderNotes();
        alert('Đã bỏ bảo vệ mật khẩu.');
      } else if (pwd !== null) {
        alert('Mật khẩu không đúng.');
      }
      menuDropdown.style.display = 'none';
    };
    menuDropdown.appendChild(unlockAction);
  }
      // Add relock button if note is locked and _unlocked
    if (note.locked && note._unlocked) {
      const relockAction = document.createElement('button');
      relockAction.className = 'menu-action text-warning';
      relockAction.textContent = 'Khóa lại';
      relockAction.onclick = () => {
        delete notes[index]._unlocked;
        saveNotes();
        renderNotes();
        menuDropdown.style.display = 'none';
      };
      menuDropdown.appendChild(relockAction);
    }

  // Label checkboxes
  const labelTitle = document.createElement('div');
  labelTitle.className = 'px-3 py-1 text-muted';
  labelTitle.textContent = 'Nhãn:';
  menuDropdown.appendChild(labelTitle);

  labels.forEach(lbl => {
    const labelOption = document.createElement('label');
    const cb = document.createElement('input');
    cb.type = 'checkbox';
    cb.checked = (note.tags || []).includes(lbl);
    cb.onchange = () => {
      if (cb.checked) {
        if (!notes[index].tags) notes[index].tags = [];
        if (!notes[index].tags.includes(lbl)) notes[index].tags.push(lbl);
      } else {
        notes[index].tags = (notes[index].tags || []).filter(t => t !== lbl);
      }
      notes[index].updatedAt = Date.now();
      saveNotes();
      renderNotes();
    };
    labelOption.appendChild(cb);
    labelOption.appendChild(document.createTextNode(lbl));
    menuDropdown.appendChild(labelOption);
  });

  // Upload photo
  const fileInput = document.createElement('input');
  fileInput.type = 'file';
  fileInput.accept = 'image/*';
  fileInput.multiple = true;
  fileInput.style.display = 'none';
  fileInput.onchange = (event) => {
    const files = Array.from(event.target.files);
    const readerPromises = files.map(file => {
      return new Promise(resolve => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.readAsDataURL(file);
      });
    });
    Promise.all(readerPromises).then(images => {
      if (!notes[index].images) notes[index].images = [];
      notes[index].images.push(...images);
      notes[index].updatedAt = Date.now();
      saveNotes();
      if (!isOwner && sharedItem && sharedItem.permission === 'edit') {
        syncBackToOwner(notes[index]);
      }
      renderNotes();
    });
    menuDropdown.style.display = 'none';
  };

  const fileBtn = document.createElement('button');
  fileBtn.type = 'button';
  fileBtn.className = 'menu-action';
  fileBtn.innerHTML = '<i class="fa fa-image me-2"></i>Thêm ảnh';
  fileBtn.onclick = (e) => {
    e.preventDefault();
    fileInput.click();
  };
  if (canEdit) {
    menuDropdown.appendChild(fileBtn);
    menuDropdown.appendChild(fileInput);
  }

  // Delete note
  const deleteAction = document.createElement('button');
  deleteAction.className = 'menu-action text-danger';
  deleteAction.textContent = 'Xóa ghi chú';
  deleteAction.onclick = () => {
    // Chỉ kiểm tra khóa nếu là ghi chú của chủ sở hữu
    if (!isSharedNote && note.locked && !note._unlocked) {
      alert('Bạn cần mở khóa ghi chú trước khi xóa.');
      menuDropdown.style.display = 'none';
      return;
    }
    if (confirm('Bạn có chắc chắn muốn xóa ghi chú này không?')) {
      deleteNote(note.id, isSharedNote);
    }
    menuDropdown.style.display = 'none';
  };
  menuDropdown.appendChild(deleteAction);

  // Show/hide menu
  menuBtn.onclick = (e) => {
    e.stopPropagation();
    document.querySelectorAll('.note-menu-dropdown').forEach(d => d.style.display = 'none');
    menuDropdown.style.display = 'block';
  };
  document.addEventListener('click', () => { menuDropdown.style.display = 'none'; });

    // Hiển thị avatar chia sẻ nếu có
    const shareInfoDiv = document.createElement('div');
    shareInfoDiv.style.display = 'flex';
    shareInfoDiv.style.justifyContent = 'flex-end';
    shareInfoDiv.style.alignItems = 'center';
    shareInfoDiv.style.gap = '6px';
    shareInfoDiv.style.marginTop = '2px';
    shareInfoDiv.style.marginRight = 'em';

    if (note.shared && note.shared.length > 0) {
      if (note.owner === userEmail) {
        // Chủ sở hữu: hiển thị avatar người nhận
        note.shared.forEach(item => {
          const avatar = item.avatar && item.avatar !== ''
            ? item.avatar
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(item.email)}&size=24`;
          shareInfoDiv.innerHTML += `<img src="${avatar}" title="${item.email}" style="width:24px;height:24px;border-radius:50%;">`;
        });
      } else {
        // Người nhận: hiển thị avatar chủ sở hữu
        const avatar = note.ownerAvatar && note.ownerAvatar !== ''
          ? note.ownerAvatar
          : `https://ui-avatars.com/api/?name=${encodeURIComponent(note.owner)}&size=24`;
        shareInfoDiv.innerHTML += `<img src="${avatar}" title="Chủ sở hữu: ${note.owner}" style="width:24px;height:24px;border-radius:50%;">`;
      }
      shareInfoDiv.innerHTML += `<i class="fas fa-share-alt text-primary ms-2" title="Đã chia sẻ"></i>`;
    }

    
    
    card.appendChild(shareInfoDiv);

  // Image preview with delete button
  const imagePreview = document.createElement('div');
  if (note.images) {
    note.images.forEach((img, imgIdx) => {
      const imageWrapper = document.createElement('span');
      imageWrapper.style.position = 'relative';
      imageWrapper.style.display = 'inline-block';
      imageWrapper.style.margin = '5px';

      const imageEl = document.createElement('img');
      imageEl.src = img;
      imageEl.style.maxWidth = '100px';
      imageEl.style.borderRadius = '10px';

      // Delete photo button
      const delBtn = document.createElement('button');
      delBtn.type = 'button';
      delBtn.innerHTML = '❌';
      delBtn.title = 'Xóa ảnh';
      delBtn.style.position = 'absolute';
      delBtn.style.top = '2px';
      delBtn.style.right = '2px';
      delBtn.style.background = 'rgba(255,255,255,0.7)';
      delBtn.style.border = 'none';
      delBtn.style.borderRadius = '50%';
      delBtn.style.cursor = 'pointer';
      delBtn.style.padding = '2px 6px';
      delBtn.onclick = (e) => {
        e.stopPropagation();
        if (!isSharedNote) {
          notes[index].images.splice(imgIdx, 1);
          notes[index].updatedAt = Date.now();
          saveNotes();
        } else {
          let myNotes = JSON.parse(localStorage.getItem(notesKey)) || [];
          const myIdx = myNotes.findIndex(n => n.id === note.id);
          if (myIdx !== -1) {
            myNotes[myIdx].images.splice(imgIdx, 1);
            myNotes[myIdx].updatedAt = Date.now();
            localStorage.setItem(notesKey, JSON.stringify(myNotes));
          }
        }
        renderNotes();
      };

      imageWrapper.appendChild(imageEl);
      imageWrapper.appendChild(delBtn);
      imagePreview.appendChild(imageWrapper);
    });
  }

      
  noteToolbar.appendChild(menuBtn);
      noteToolbar.appendChild(menuDropdown);
      card.appendChild(titleInput);
      card.appendChild(labelList);
      card.appendChild(contentInput);
      card.appendChild(noteToolbar);

      card.appendChild(imagePreview);
      card.appendChild(updatedAtDiv);
      card.appendChild(shareInfoDiv);

      noteToolbar.appendChild(menuBtn);
      noteToolbar.appendChild(menuDropdown);

      div.appendChild(card);
      return div;
    }
      function renderNotes() {
  notes = JSON.parse(localStorage.getItem(notesKey)) || [];
  noteContainer.innerHTML = '';
  
  // Lấy tất cả ghi chú được chia sẻ
  const sharedNotes = getAllSharedNotes();
  
  // Kết hợp notes và sharedNotes, loại bỏ trùng lặp
  const allNotesMap = {};
  notes.forEach(n => allNotesMap[n.id] = n);
  sharedNotes.forEach(n => {
    if (!allNotesMap[n.id] && (n.shared || []).some(s => s.email === userEmail)) {
      allNotesMap[n.id] = n;
    }
  });
  
  let allNotes = Object.values(allNotesMap);

  // Lọc theo tag và search
  const filtered = allNotes.filter(n => {
    const matchesTag = currentTag ? (n.tags || []).includes(currentTag) : true;
    const matchesSearch = searchQuery ? 
      (n.title.toLowerCase().includes(searchQuery) || 
       n.content.toLowerCase().includes(searchQuery)) : true;
    return matchesTag && matchesSearch;
  });

  // Sắp xếp và render
  const locked = filtered.filter(n => n.locked && !n._unlocked)
    .sort((a, b) => b.updatedAt - a.updatedAt);
  const pinned = filtered.filter(n => n.pinned && !(n.locked && !n._unlocked))
    .sort((a, b) => b.updatedAt - a.updatedAt);
  const others = filtered.filter(n => !n.pinned && !(n.locked && !n._unlocked))
    .sort((a, b) => b.updatedAt - a.updatedAt);

  [...pinned, ...others, ...locked].forEach(note => {
    const isSharedNote = !notes.some(n => n.id === note.id);
    const index = isSharedNote ? 
      sharedNotes.findIndex(n => n.id === note.id) : 
      notes.findIndex(n => n.id === note.id);
    
    const noteEl = createNoteElement(note, index, isSharedNote);
    noteContainer.appendChild(noteEl);
  });
}
      function syncSharedNote(note) {
        (note.shared || []).forEach(item => {
          const receiverKey = 'notes_' + item.email;
          // Xóa sạch mọi bản ghi chú cùng id trước khi thêm mới
          let receiverNotes = JSON.parse(localStorage.getItem(receiverKey)) || [];
          receiverNotes = receiverNotes.filter(n => n.id !== note.id);

          // Chỉ gửi các trường cần thiết, KHÔNG gửi locked, password, _unlocked
          const sharedNote = {
            id: note.id,
            title: note.title,
            content: note.content,
            owner: note.owner,
            ownerAvatar: note.ownerAvatar,
            shared: [
              {
                email: item.email,
                permission: item.permission,
                avatar: item.avatar
              }
            ],
            createdAt: note.createdAt,
            updatedAt: note.updatedAt
          };

          receiverNotes.push(sharedNote);
          localStorage.setItem(receiverKey, JSON.stringify(receiverNotes));
        });
      }
      function syncBackToOwner(note) {
        const ownerKey = 'notes_' + note.owner;
        let ownerNotes = JSON.parse(localStorage.getItem(ownerKey)) || [];
        const idx = ownerNotes.findIndex(n => n.id === note.id);
        if (idx !== -1) {
          ownerNotes[idx] = JSON.parse(JSON.stringify(note));
          localStorage.setItem(ownerKey, JSON.stringify(ownerNotes));
        }
      }
      
      //Share note
      let shareNoteIdx = null;

      function openSharePopup(note, idx) {
        shareNoteIdx = idx;
        document.getElementById('shareEmailInput').value = '';
        document.getElementById('sharePermissionInput').value = 'read';
        renderSharedList(note);
        document.getElementById('shareNoteModal').classList.add('show');
      }

      function closeSharePopup() {
        document.getElementById('shareNoteModal').classList.remove('show');
      }

      function renderSharedList(note) {
        const sharedList = document.getElementById('sharedList');
        sharedList.innerHTML = '';
        (note.shared || []).forEach((item, idx) => {
          sharedList.innerHTML += `
            <div class="share-user-row mb-1">
              <div class="share-user-info">
                <img src="${item.avatar && item.avatar !== '' ? item.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(item.email)}&size=32`}" class="share-avatar me-2" alt="avatar">
                <span class="share-email">${item.email}</span>
                <span class="share-badge ${item.permission === 'edit' ? 'edit' : 'read'}">
                  ${item.permission === 'edit' ? 'Chỉnh sửa' : 'Chỉ đọc'}
                </span>
              </div>
              <button class="btn btn-outline-danger btn-sm btn-revoke" onclick="removeShareUser(${idx})">
                <i class="fas fa-user-slash me-1"></i>Thu hồi
              </button>
            </div>
          `;
        });
      }

      function submitShare() {
        const email = document.getElementById('shareEmailInput').value.trim();
        const permission = document.getElementById('sharePermissionInput').value;
        if (!email) return alert('Vui lòng nhập email!');
        if (email === userEmail) return alert('Không thể chia sẻ cho chính bạn!');
        const note = notes[shareNoteIdx];
        note.shared = note.shared || [];
        if (note.shared.some(s => s.email === email)) return alert('Email này đã được chia sẻ!');

        let receiverAvatar = '';
        // Thử lấy avatar thật từ localStorage (nếu có)
        receiverAvatar = localStorage.getItem('user_avatar_' + email) 
          || `https://ui-avatars.com/api/?name=${encodeURIComponent(email)}&size=24`;

        note.shared.push({ email, permission, avatar: receiverAvatar });

        saveNotes();
        renderSharedList(note);
        renderNotes();
        document.getElementById('shareEmailInput').value = '';

        // --- Đồng bộ nhãn cho người nhận ---
        const receiverLabelsKey = 'labels_' + email;
        let receiverLabels = JSON.parse(localStorage.getItem(receiverLabelsKey)) || [];
        (note.tags || []).forEach(tag => {
          if (tag && !receiverLabels.includes(tag)) receiverLabels.push(tag);
        });
        localStorage.setItem(receiverLabelsKey, JSON.stringify(receiverLabels));
      }

      function removeShareUser(idx) {
        const note = notes[shareNoteIdx];
        const removed = note.shared.splice(idx, 1)[0];
        saveNotes();
        renderSharedList(note);
        renderNotes();

        // --- Xóa khỏi notes của người nhận ---
        if (removed && removed.email) {
          const receiverKey = 'notes_' + removed.email;
          let receiverNotes = JSON.parse(localStorage.getItem(receiverKey)) || [];
          receiverNotes = receiverNotes.filter(n => n.id !== note.id);
          localStorage.setItem(receiverKey, JSON.stringify(receiverNotes));
        }
      }

      addNoteBtn.addEventListener('click', () => {
        notes.unshift({
          id: Date.now(),
          owner: userEmail,
          ownerAvatar: "<?php echo htmlspecialchars($_SESSION['user']['avatar'] ?? ''); ?>",
          title: '',
          content: '',
          tags: [],
          images: [],
          pinned: false,
          locked: false,
          password: '',
          createdAt: Date.now(),
          updatedAt: Date.now(),
          shared: [],
          color: '#fff',
          fontSize: '16px'
        });
        saveNotes();
        renderNotes();
      });

      document.getElementById('allNotesBtn').onclick = function() {
        currentTag = '';
        renderNotes();
        // Nếu có highlight nhãn đang chọn, hãy xóa class active ở các nhãn khác và thêm vào đây
        document.querySelectorAll('#labelSidebar .list-group-item').forEach(li => li.classList.remove('active'));
        this.classList.add('active');
      };
      document.getElementById('logoAllNotesBtn').onclick = function() {
        currentTag = '';
        renderNotes();
        // Highlight lại nút "Ghi chú" ở sidebar
        document.querySelectorAll('#labelSidebar .list-group-item').forEach(li => li.classList.remove('active'));
        document.getElementById('allNotesBtn').classList.add('active');
      };

      document.getElementById('sidebarToggle').onclick = function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
      };

      gridBtn.addEventListener('click', () => {
        noteContainer.classList.add('grid-view');
        noteContainer.classList.remove('list-view');
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
      });

      listBtn.addEventListener('click', () => {
        noteContainer.classList.add('list-view');
        noteContainer.classList.remove('grid-view');
        listBtn.classList.add('active');
        gridBtn.classList.remove('active');
      });

      searchInput.addEventListener('input', () => {
        searchQuery = searchInput.value.toLowerCase();
        renderNotes();
      });

      toggleDarkModeBtn.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
      });

      document.getElementById('refreshNotesBtn').onclick = function() {
        notes = JSON.parse(localStorage.getItem(notesKey)) || [];
        renderNotes();
      };

      window.addEventListener('storage', function(e) {
        // Nếu notes của user hiện tại bị thay đổi (do người nhận xóa ghi chú)
        if (e.key && e.key.startsWith('notes_')) {
          // Luôn cập nhật lại notes từ localStorage và render lại giao diện
          notes = JSON.parse(localStorage.getItem(notesKey)) || [];
          renderNotes();
          renderLabels();
        }
      });

      async function syncNotesToServer() {
        const userEmail = "<?php echo $_SESSION['user']['email']; ?>";
        const notesKey = 'notes_' + userEmail;
        let notes = JSON.parse(localStorage.getItem(notesKey)) || [];

        // Lọc notes chưa sync
        const notesToSync = notes.filter(note => !note.synced);

        if (notesToSync.length === 0) {
          console.log('Không có ghi chú nào cần đồng bộ.');
          return;
        }

        try {
          for (const note of notesToSync) {
            const response = await fetch('sync_note.php', {  // <-- Đường dẫn API bạn cần thay đổi phù hợp
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(note)
            });

            if (response.ok) {
              // Nếu đồng bộ thành công, đánh dấu đã sync
              note.synced = true;
              console.log(`Đã đồng bộ ghi chú id=${note.id}`);
            } else {
              console.error(`Đồng bộ ghi chú id=${note.id} thất bại với mã ${response.status}`);
            }
          }
          // Lưu lại notes đã được đánh dấu synced
          localStorage.setItem(notesKey, JSON.stringify(notes));
          console.log('Đồng bộ ghi chú hoàn tất.');
        } catch (error) {
          console.error('Lỗi khi đồng bộ ghi chú:', error);
        }
      }



      window.addEventListener('online', () => {
        console.log('Mạng online, bắt đầu đồng bộ ghi chú...');
        syncNotesToServer();
      });

      gridBtn.addEventListener('click', () => {
  noteContainer.classList.add('switching');
  setTimeout(() => {
    noteContainer.classList.add('grid-view');
    noteContainer.classList.remove('list-view');
    gridBtn.classList.add('active');
    listBtn.classList.remove('active');
    noteContainer.classList.remove('switching');
  }, 180);
});

listBtn.addEventListener('click', () => {
  noteContainer.classList.add('switching');
  setTimeout(() => {
    noteContainer.classList.add('list-view');
    noteContainer.classList.remove('grid-view');
    listBtn.classList.add('active');
    gridBtn.classList.remove('active');
    noteContainer.classList.remove('switching');
  }, 180);
});

      renderLabels();
      renderNotes();
  </script>

  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function() {
        navigator.serviceWorker.register('/note_app/sw.js').then(function(registration) {
          console.log('ServiceWorker đăng ký thành công với phạm vi: ', registration.scope);
        }).catch(function(error) {
          console.log('ServiceWorker đăng ký thất bại:', error);
        });
      });
    }
  </script>


  <div class="modal" tabindex="-1" id="shareNoteModal">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-share-alt me-2"></i>Chia sẻ ghi chú</h5>
        <button type="button" class="btn-close btn-close-white" onclick="closeSharePopup()"></button>
      </div>
      <div class="modal-body">
        <form class="row g-2 align-items-end mb-3" onsubmit="event.preventDefault();submitShare();">
          <div class="col-12 col-md-7">
            <label for="shareEmailInput" class="form-label mb-1">Email người nhận</label>
            <input type="email" id="shareEmailInput" class="form-control" placeholder="Nhập email người nhận">
          </div>
          <div class="col-8 col-md-3">
            <label for="sharePermissionInput" class="form-label mb-1">Quyền</label>
            <select id="sharePermissionInput" class="form-select">
              <option value="read">Chỉ đọc</option>
              <option value="edit">Chỉnh sửa</option>
            </select>
          </div>
          <div class="col-12 col-md-4 d-grid">
            <button class="btn btn-primary" type="submit"><i class="fas fa-user-plus me-1"></i>Chia sẻ</button>
          </div>
        </form>
        <div>
          <h6 class="mb-2">Đã chia sẻ với:</h6>
          <div id="sharedList"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
#shareNoteModal { display:none; background:rgba(0,0,0,0.2); position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:3000; align-items:center; justify-content:center; }
#shareNoteModal.show { display:flex; }
#sharedList .share-user-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-radius: 8px;
  padding: 8px 0;
  transition: background 0.2s;
}
#sharedList .share-user-row:hover {
  background: #f8f9fa;
}
#sharedList .share-user-info {
  display: flex;
  align-items: center;
  gap: 10px;
}
#sharedList .share-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid #ddd;
  background: #fff;
}
#sharedList .share-email {
  font-weight: 500;
  color: #333;
}
#sharedList .share-badge {
  font-size: 0.85em;
  margin-left: 8px;
  padding: 3px 10px;
  border-radius: 12px;
}
#sharedList .share-badge.read {
  background: #e9ecef;
  color: #6c757d;
}
#sharedList .share-badge.edit {
  background: #d1e7dd;
  color: #198754;
}
#sharedList .btn-revoke {
  padding: 4px 14px;
  font-size: 0.95em;
  border-radius: 20px;
}
.keep-popup {
  animation: fadeInUp 0.2s;
}
.note-container .note-item {
  transition:
    width 0.35s cubic-bezier(.4,0,.2,1),
    transform 0.35s cubic-bezier(.4,0,.2,1),
    opacity 0.25s cubic-bezier(.4,0,.2,1),
    box-shadow 0.22s cubic-bezier(.4,0,.2,1);
  will-change: width, transform, opacity;
}

.note-container {
  transition: opacity 0.25s cubic-bezier(.4,0,.2,1);
}
.note-container.switching {
  opacity: 0.3;
  pointer-events: none;
}
.note-card,
.note-toolbar {
  overflow: visible !important;
  position: relative;
}
.note-toolbar > div,
.note-toolbar > .note-menu-dropdown {
  z-index: 1001;
}


</style>

<footer class="footer mt-5 py-3 bg-light border-top shadow-sm">
  <div class="container text-center">
    <div class="mb-2">
      <a href="https://github.com/Devpool314/Web-Final" target="_blank" class="text-decoration-none text-primary fw-bold">
        <i class="fab fa-github me-1"></i>Noti
      </a>
      <span class="mx-2 text-muted">|</span>
      <a href="mailto:support@example.com" class="text-decoration-none text-secondary">
        <i class="fas fa-envelope me-1"></i>Liên hệ hỗ trợ
      </a>
    </div>
    <div class="small text-muted">
      &copy; <?php echo date('Y'); ?> Note App. Được phát triển bởi Noti Comapny.
    </div>
  </div>
</footer>

<script>
// WebSocket connection
let ws;
let wsReconnectAttempts = 0;
const MAX_RECONNECT_ATTEMPTS = 5;

const getWebSocketUrl = () => {
  // Thử kết nối với một số địa chỉ khác nhau
  const urls = [
    'ws://localhost:8080',
    'ws://127.0.0.1:8080',
    `ws://${window.location.hostname}:8080`
  ];
  return urls[wsReconnectAttempts % urls.length];
};

const wsConnect = () => {
  if (wsReconnectAttempts >= MAX_RECONNECT_ATTEMPTS) {
    console.error('Max WebSocket reconnection attempts reached');
    return;
  }

  try {
    const wsUrl = getWebSocketUrl();
    console.log(`Attempting to connect to WebSocket at ${wsUrl}`);
    ws = new WebSocket(wsUrl);
    
    ws.onopen = () => {
      console.log('WebSocket connected successfully');
      wsReconnectAttempts = 0; // Reset counter on successful connection
      
      // Đăng ký email người dùng
      ws.send(JSON.stringify({
        type: 'register',
        email: userEmail
      }));
    };

    ws.onmessage = (e) => {
      try {
        const data = JSON.parse(e.data);
        console.log('WebSocket message received:', data);

        switch(data.type) {
          case 'connection_success':
            console.log('WebSocket connection confirmed:', data.message);
            break;
            
          case 'note_updated':
            // Cập nhật note trong localStorage
            const updatedNote = data.note;
            let myNotes = JSON.parse(localStorage.getItem(notesKey)) || [];
            const noteIndex = myNotes.findIndex(n => n.id === updatedNote.id);
            
            if (noteIndex !== -1) {
              myNotes[noteIndex] = updatedNote;
              localStorage.setItem(notesKey, JSON.stringify(myNotes));
              renderNotes();
            }
            break;

          case 'error':
            console.error('Server error:', data.message);
            break;
        }
      } catch (error) {
        console.error('Error processing WebSocket message:', error);
      }
    };

    ws.onclose = (e) => {
      console.log('WebSocket disconnected:', e.reason);
      wsReconnectAttempts++;
      
      // Thử kết nối lại với thời gian chờ tăng dần
      const timeout = Math.min(1000 * Math.pow(2, wsReconnectAttempts), 10000);
      console.log(`Attempting to reconnect in ${timeout/1000} seconds... (Attempt ${wsReconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})`);
      
      setTimeout(wsConnect, timeout);
    };

    ws.onerror = (error) => {
      console.error('WebSocket error:', error);
      // Không đóng kết nối ở đây, để onclose xử lý việc kết nối lại
    };
  } catch (error) {
    console.error('Error creating WebSocket connection:', error);
    wsReconnectAttempts++;
    setTimeout(wsConnect, 3000);
  }
};

// Kết nối WebSocket khi trang load
document.addEventListener('DOMContentLoaded', () => {
  setTimeout(wsConnect, 1000); // Đợi 1 giây trước khi kết nối
});

// Sửa lại hàm saveNotes để gửi cập nhật qua WebSocket
function saveNotes() {
  localStorage.setItem(notesKey, JSON.stringify(notes));
  notes.forEach(note => {
    if (note.shared && note.shared.length > 0) {
      // Gửi cập nhật qua WebSocket
      if (ws && ws.readyState === WebSocket.OPEN) {
        try {
          ws.send(JSON.stringify({
            type: 'note_update',
            note: note
          }));
        } catch (error) {
          console.error('Error sending note update:', error);
        }
      } else {
        console.warn('WebSocket not connected. Update will be synced when connection is restored.');
      }
      syncSharedNote(note);
    }
  });
}

// Thêm event listener để đóng WebSocket khi rời trang
window.addEventListener('beforeunload', () => {
  if (ws) {
    ws.close();
  }
});
</script>

</body>
</html>