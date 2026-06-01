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
    <title>Политика конфиденциальности и Согласие на обработку данных — BRONIC.RU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="icon" href="../img/bronic.png" type="image/png">
    <style>
        .legal-content {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            line-height: 1.7;
            color: #334155;
        }
        .legal-content h1 {
            color: #1e293b;
            font-weight: 800;
            margin-bottom: 30px;
        }
        .legal-content h2 {
            color: #1e293b;
            font-weight: 700;
            font-size: 1.25rem;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .legal-content p, .legal-content li {
            font-size: 1rem;
        }
        .legal-header {
            background: linear-gradient(135deg, #fe496a, #ff8c42);
            padding: 60px 0;
            color: white;
            margin-bottom: -40px;
        }
    </style>
</head>
<body style="background: #f8fafc;">
    <?php include 'inc/_nav.php'; ?>

    <header class="legal-header text-center">
        <div class="container">
            <h1 class="display-5 fw-bold mb-2">Юридическая информация</h1>
            <p class="lead opacity-75">Правила сервиса и защита ваших данных</p>
        </div>
    </header>

    <main class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="legal-content">
                    <h1>Согласие на обработку персональных данных</h1>
                    <p class="text-muted small mb-4">Редакция от 1 июня 2026 года</p>

                    <p>Пользователь, оставляя заявку на интернет-сайте <strong>BRONIC.RU</strong>, принимает настоящее Согласие на обработку персональных данных (далее – Согласие).</p>

                    <h2>1. Какие данные мы обрабатываем</h2>
                    <p>Действуя свободно, своей волей и в своем интересе, Пользователь дает свое согласие на обработку следующих персональных данных:</p>
                    <ul>
                        <li>Фамилия, Имя, Отчество;</li>
                        <li>Номер контактного телефона;</li>
                        <li>Адрес электронной почты (email);</li>
                        <li>Паспортные данные (для заключения договора краткосрочного найма жилого помещения);</li>
                        <li>Информация о выбранных объектах недвижимости и датах бронирования.</li>
                    </ul>

                    <h2>2. Цели обработки</h2>
                    <p>Персональные данные Пользователя обрабатываются исключительно в целях:</p>
                    <ul>
                        <li>Предоставления услуг по бронированию жилья;</li>
                        <li>Идентификации стороны в рамках договоров с Сервисом;</li>
                        <li>Связи с Пользователем для подтверждения бронирования;</li>
                        <li>Выполнения требований законодательства РФ.</li>
                    </ul>

                    <h2>3. Безопасность и хранение</h2>
                    <p>Сервис принимает необходимые и достаточные организационные и технические меры для защиты персональной информации Пользователя от неправомерного или случайного доступа, уничтожения, изменения, блокирования, копирования, распространения, а также от иных неправомерных действий с ней третьих лиц.</p>
                    <p>Паспортные данные хранятся в зашифрованном виде с использованием современных алгоритмов криптографии.</p>

                    <h2>4. Срок действия</h2>
                    <p>Настоящее согласие действует бессрочно с момента предоставления данных и может быть отозвано Пользователем путем направления письменного заявления на электронный адрес службы поддержки Сервиса.</p>

                    <hr class="my-5">

                    <h1>Правила сервиса (Оферта)</h1>
                    <h2>1. Бронирование и оплата</h2>
                    <p>Бронирование считается подтвержденным после внесения предоплаты или полной оплаты через платежную систему ЮKassa. Сервис гарантирует безопасность транзакций.</p>

                    <h2>2. Условия отмены</h2>
                    <p>Бесплатная отмена возможна в сроки, указанные для каждого конкретного объекта недвижимости. При несвоевременной отмене может взиматься штраф в размере стоимости первых суток проживания.</p>

                    <h2>3. Ответственность</h2>
                    <p>BRONIC.RU является платформой-агрегатором и не несет прямой ответственности за качество предоставляемых услуг собственниками жилья, однако оказывает всестороннюю поддержку в решении спорных ситуаций.</p>
                    
                    <div class="text-center mt-5">
                        <a href="booking.php" class="btn btn-danger px-4 py-2" style="border-radius:10px;">
                            <i class="bi bi-arrow-left me-2"></i>Вернуться к бронированию
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'inc/_footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="main.js"></script>
</body>
</html>
