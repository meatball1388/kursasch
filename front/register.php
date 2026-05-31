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
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="icon" href="../img/bronic.png" type="image/png">
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
                                <div id="emailError" class="text-danger small mt-1" style="display: none;"></div>
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
                                    Я согласен с <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#rulesModal">правилами сервиса</a>
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
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#rulesModal" class="text-decoration-none">Условиями использования</a>
                        и
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#personalDataModal" class="text-decoration-none">Политикой конфиденциальности</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
    .legal-doc-container {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #333;
        line-height: 1.6;
    }
    .legal-doc-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: #000;
        border-bottom: 2px solid #fe496a;
        padding-bottom: 0.5rem;
        display: inline-block;
    }
    .legal-section-title {
        font-size: 1rem;
        font-weight: 600;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        color: #000;
    }
    .legal-text {
        font-size: 0.875rem;
        margin-bottom: 0.75rem;
        text-align: justify;
    }
    .legal-list {
        list-style-type: none;
        padding-left: 0;
        margin-bottom: 1rem;
    }
    .legal-list-item {
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        padding-left: 1.5rem;
        position: relative;
    }
    .legal-list-item::before {
        content: "•";
        color: #fe496a;
        position: absolute;
        left: 0;
        font-weight: bold;
    }
    </style>

    <!-- Модальное окно: Правила сервиса -->
    <div class="modal fade" id="rulesModal" tabindex="-1" aria-labelledby="rulesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="modal-title d-none" id="rulesModalLabel">Пользовательское соглашение</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-0 legal-doc-container">
                    <div class="legal-doc-title">Пользовательское соглашение (Оферта)</div>
                    
                    <div class="legal-section-title">1. Предмет соглашения</div>
                    <p class="legal-text">1.1. Настоящее Соглашение является публичной офертой и регулирует порядок использования Сервиса BRONIC.RU Пользователями.</p>
                    <p class="legal-text">1.2. Использование Сервиса означает полное и безоговорочное принятие Пользователем условий настоящего Соглашения.</p>
                    
                    <div class="legal-section-title">2. Права и обязанности сторон</div>
                    <ul class="legal-list">
                        <li class="legal-list-item">2.1. Пользователь обязуется предоставлять достоверную и актуальную информацию при регистрации и оформлении бронирования.</li>
                        <li class="legal-list-item">2.2. Администрация обязуется обеспечивать конфиденциальность предоставленных данных и работоспособность Сервиса.</li>
                        <li class="legal-list-item">2.3. Пользователю запрещено использовать Сервис для совершения мошеннических действий или в иных противоправных целях.</li>
                    </ul>

                    <div class="legal-section-title">3. Ограничение ответственности</div>
                    <p class="legal-text">3.1. Сервис предоставляет платформу для взаимодействия гостей и арендодателей и не является стороной договора найма жилого помещения.</p>
                    <p class="legal-text">3.2. Сервис не несет ответственности за качество услуг, предоставляемых объектами размещения, и за достоверность описаний, предоставленных владельцами.</p>

                    <div class="legal-section-title">4. Прочие условия</div>
                    <p class="legal-text">4.1. Администрация вправе вносить изменения в настоящее Соглашение в одностороннем порядке.</p>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal" style="background-color: #fe496a; border-radius: 6px; border: none;">Понятно</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно: Персональные данные -->
    <div class="modal fade" id="personalDataModal" tabindex="-1" aria-labelledby="personalDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="modal-title d-none" id="personalDataModalLabel">Политика конфиденциальности</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-0 legal-doc-container">
                    <div class="legal-doc-title">Политика конфиденциальности</div>
                    
                    <p class="legal-text">Администрация Сервиса BRONIC.RU с уважением относится к праву каждого Пользователя на конфиденциальность.</p>
                    
                    <div class="legal-section-title">1. Сбор и использование информации</div>
                    <p class="legal-text">1.1. Мы собираем только те данные, которые необходимы для предоставления качественных услуг бронирования: ФИО, адрес электронной почты, контактный телефон.</p>
                    
                    <div class="legal-section-title">2. Защита данных</div>
                    <p class="legal-text">2.1. Мы применяем современные технические и организационные меры для защиты ваших данных от несанкционированного доступа, изменения или уничтожения.</p>

                    <div class="legal-section-title">3. Передача третьим лицам</div>
                    <p class="legal-text">3.1. Ваши данные могут быть переданы владельцу объекта размещения исключительно для целей подтверждения вашего бронирования.</p>
                    <p class="legal-text">3.2. Мы никогда не передаем ваши контактные данные рекламным агентствам для массовых рассылок без вашего явного согласия.</p>

                    <div class="legal-section-title">4. Файлы Cookie</div>
                    <p class="legal-text">4.1. Мы используем файлы cookie для сохранения ваших предпочтений и повышения удобства работы с сайтом.</p>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 6px;">Закрыть</button>
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
                
                // Очистка предыдущих ошибок
                $('#registerEmail').removeClass('is-invalid');
                $('#emailError').hide().text('');

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
                const data = {};
                $.each(formDataArray, function() {
                    data[this.name] = this.value;
                });

                $.ajax({
                    url: 'http://' + (window.location.hostname || 'localhost') + ':8000/register',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    success: function(response) {
                        if (response.message === 'ok') {
                            // Автоматически входим после регистрации
                            $.ajax({
                                url: 'http://' + (window.location.hostname || 'localhost') + ':8000/login',
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
                                                id: loginResp.id,
                                                email: loginResp.email,
                                                name: loginResp.name,
                                                surname: loginResp.surname,
                                                role: loginResp.role,
                                                phone: loginResp.phone,
                                                passport: loginResp.passport
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
                        } else if (response.message === 'почта занята') {
                            $('#registerEmail').addClass('is-invalid');
                            $('#emailError').text('Этот Email уже зарегистрирован').show();
                            $btn.prop('disabled', false).html(originalText);
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
                        
                        if (errorMsg.toLowerCase().includes('почта') || errorMsg.toLowerCase().includes('email')) {
                            $('#registerEmail').addClass('is-invalid');
                            $('#emailError').text(errorMsg).show();
                        } else {
                            alert(errorMsg);
                        }
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Убираем ошибку при начале ввода
            $('#registerEmail').on('input', function() {
                $(this).removeClass('is-invalid');
                $('#emailError').hide();
            });
        });//doc ready

    </script>
</body>

</html>
