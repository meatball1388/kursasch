<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в аккаунт - BRONIC.RU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-light">

    <?php include 'inc/_nav.php'; ?>

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold" style="color: #fe496a;">BRONIC.RU</h2>
                            <p class="text-muted">Вход в аккаунт</p>
                        </div>

                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="loginEmail" class="form-label">Почта</label>
                                <input type="email" value="a@123" class="form-control form-control-lg" id="loginEmail" name="email" placeholder="example@mail.ru" required>
                            </div>

                            <div class="mb-3">
                                <label for="loginPassword" class="form-label">Пароль</label>
                                <div class="position-relative">
                                    <input type="password" value="123123" class="form-control form-control-lg" id="loginPassword" name="password" placeholder="••••••••" required>
                                    <button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y p-0" onclick="togglePassword()" style="text-decoration: none;">
                                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                                <label class="form-check-label" for="rememberMe">Запомнить меня</label>
                            </div>

                            <button type="submit" class="btn btn-danger w-100 py-3 mb-3" style="background-color: #fe496a; border: none;">Войти</button>

                            <div class="text-center">
                                <a href="register.php" class="text-decoration-none small">Регистрация</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'inc/_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function togglePassword() {
            var passwordInput = document.getElementById('loginPassword');
            var icon = document.getElementById('togglePasswordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            var $btn = $(this).find('button[type="submit"]');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Вход...');
            
            $.ajax({
                url: 'http://localhost:8000/login',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    email: $('#loginEmail').val(),
                    password: $('#loginPassword').val()
                }),
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    // return false;
                    if (response.success === 'true') {
                        // Сохраняем сессию через PHP
                        $.ajax({
                            url: 'set_session.php',
                            method: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({
                                email: response.email,
                                name: response.name,
                                surname: response.surname,
                                role: response.role
                            }),
                            success: function() {
                                console.log('session');
                                window.location.href = 'index.php';
                            }
                        });
                    } else {
                        alert(response.message || 'Ошибка входа');
                        $btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Ошибка сервера';
                    try {
                        var resp = xhr.responseJSON;
                        errorMsg = resp ? (resp.message || resp.error || errorMsg) : errorMsg;
                    } catch(e) {}
                    alert(errorMsg);
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });
        });
    </script>
</body>
</html>
