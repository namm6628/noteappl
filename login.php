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
</style> 