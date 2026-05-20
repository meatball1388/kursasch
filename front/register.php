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
    <title>Регистрация - BRONIC.RU</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <!-- Ваш CSS -->
    <link rel="stylesheet" href="/custom.css">
</head>

<body class="bg-light">

    <!-- Навигация -->
    <?php include 'inc/_nav.php'; ?>

    <!-- Форма регистрации -->
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow">
                    <div class="card-body p-5">
                        <!-- Логотип -->
                        <div class="text-center mb-4">
                            <h2 class="fw-bold" style="color: #fe496a;">BRONIC.RU</h2>
                            <p class="text-muted">Создание аккаунта</p>
                        </div>

                        <!-- Форма -->
                        <form id="registerForm">
                            <!-- Имя -->
                            <div class="mb-3">
                                <label for="registerName" class="form-label">Имя <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="registerName" name="name"
                                    placeholder="Иван" required>
                            </div>

                            <!-- Фамилия -->
                            <div class="mb-3">
                                <label for="registerSurname" class="form-label">Фамилия <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="registerSurname" name="surname"
                                    placeholder="Иванов" required>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="registerEmail" class="form-label">Почта <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-lg" id="registerEmail" name="email"
                                    placeholder="example@mail.ru" required>
                            </div>

                            <!-- Пароль -->
                            <div class="mb-3">
                                <label for="registerPassword" class="form-label">Пароль <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" class="form-control form-control-lg" id="registerPassword" name="password"
                                        placeholder="••••••••" required>
                                    <button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y p-0"
                                        onclick="togglePassword('registerPassword', 'toggleRegisterIcon')" style="text-decoration: none;">
                                        <i class="bi bi-eye" id="toggleRegisterIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">Минимум 6 символов</div>
                            </div>

                            <!-- Повтор пароля -->
                            <div class="mb-3">
                                <label for="registerPasswordConfirm" class="form-label">Повторите пароль <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" class="form-control form-control-lg" id="registerPasswordConfirm"
                                        placeholder="••••••••" required>
                                    <button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y p-0"
                                        onclick="togglePassword('registerPasswordConfirm', 'toggleConfirmIcon')" style="text-decoration: none;">
                                        <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Согласие с правилами -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agreeRules" required>
                                <label class="form-check-label" for="agreeRules">
                                    Я согласен с <a href="#" target="_blank">правилами сервиса</a>
                                </label>
                            </div>

                            <!-- Кнопка регистрации -->
                            <button type="submit" class="btn btn-danger w-100 py-3 mb-3"
                                style="background-color: #fe496a; border: none;">
                                Зарегистрироваться
                            </button>


                            <!-- Ссылка на вход -->
                            <div class="text-center">
                                <span class="text-muted">Уже есть аккаунт?</span>
                                <a href="login.php" class="text-decoration-none small">Войти</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Дополнительная информация -->
                <div class="text-center mt-4">
                    <p class="text-muted small mb-0">
                        Регистрация означает согласие с
                        <a href="#" class="text-decoration-none">Условиями использования</a>
                        и
                        <a href="#" class="text-decoration-none">Политикой конфиденциальности</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Футер -->
    <?php include 'inc/_footer.php'; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <!-- Ваш JS -->
    <script src="main.js"></script>
    <script>
        // Показать/скрыть пароль
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

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

        // AJAX обработка формы регистрации
        $(document).ready(function() {

            $('#registerForm').on('submit', function(e) {
                e.preventDefault();

                const password = $('#registerPassword').val();
                const passwordConfirm = $('#registerPasswordConfirm').val();

                if (password !== passwordConfirm) {
                    alert('Пароли не совпадают!');
                    return false;
                }

                if (password.length < 6) {
                    alert('Пароль должен быть не менее 6 символов');
                    return false;
                }

                const $btn = $(this).find('button[type="submit"]');
                const originalText = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Регистрация...');

                let formDataArray = $(this).serializeArray();
                console.log(formDataArray);
                const data = {};
                $.each(formDataArray, function() {
                    data[this.name] = this.value;
                });
                // return false;

                $.ajax({
                    url: 'http://localhost:8000/register',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    success: function(response) {
                        if (response.message === 'ok') {
                            // Автоматически входим после регистрации
                            $.ajax({
                                url: 'http://localhost:8000/login',
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    email: data.email,
                                    password: data.password
                                }),
                                success: function(loginResp) {
                                    if (loginResp.message === 'вход успешен') {
                                        $.ajax({
                                            url: 'set_session.php',
                                            method: 'POST',
                                            contentType: 'application/json',
                                            data: JSON.stringify({
                                                email: loginResp.email,
                                                name: loginResp.name,
                                                surname: loginResp.surname,
                                                role: loginResp.role
                                            }),
                                            success: function() {
                                                window.location.href = 'index.php';
                                            }
                                        });
                                    } else {
                                        alert('Регистрация прошла успешно! Войдите в аккаунт.');
                                        window.location.href = 'login.php';
                                    }
                                },
                                error: function() {
                                    alert('Регистрация прошла успешно! Войдите в аккаунт.');
                                    window.location.href = 'login.php';
                                }
                            });
                        } else {
                            alert(response.message || 'Ошибка регистрации');
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
        });//doc ready

    </script>
</body>

</html>
