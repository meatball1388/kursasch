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
    <title>Бронирование - BRONIC.RU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="icon" href="../img/bronic.png" type="image/png">
</head>

<body>
    <?php include 'inc/_nav.php'; ?>
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 100px; z-index: 1;">
                    <div class="card-body">
                        <img id="propertyImage" src="../img/property/metro-plus.png" class="img-fluid rounded mb-3 w-100" alt="Жильё"
                            style="height: 250px; object-fit: cover;">
                        <h4 class="card-title fw-bold mb-2" id="propertyTitle">Загрузка...</h4>
                        <p class="text-muted mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i><span
                                id="propertyLocation">---</span></p>
                        <div class="d-flex gap-3 mb-3" id="propertyDetails">
                            <span class="badge bg-light text-dark"><i class="bi bi-people me-1"></i><span id="detailGuests">2</span> гостей</span>
                            <span class="badge bg-light text-dark"><i class="bi bi-door-open me-1"></i><span id="detailBedrooms">1</span> спальня</span>
                            <span class="badge bg-light text-dark"><i class="bi bi-aspect-ratio me-1"></i><span id="detailArea">45</span> м²</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Цена за ночь:</span>
                            <span class="fs-4 fw-bold text-danger" id="pricePerNight">0 ₽</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Количество ночей:</span>
                            <span class="fs-5 fw-bold" id="nightsCount">0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Проживание:</span>
                            <span class="fw-bold" id="subtotal">0 ₽</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Уборка:</span>
                            <span class="fw-bold">1 000 ₽</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Сервисный сбор:</span>
                            <span class="fw-bold">750 ₽</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-bold">Итого:</span>
                            <span class="fs-4 fw-bold text-danger" id="totalPrice">0 ₽</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold mb-4">Оформление бронирования</h3>
                        <form id="bookingForm">
                            <div class="mb-4">
                                <h5 class="mb-3"><i class="bi bi-calendar3 me-2"></i>Даты проживания</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="checkinDate" class="form-label">Заезд</label>
                                        <input type="text" class="form-control" id="checkinDate"
                                            placeholder="Выберите дату" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="checkoutDate" class="form-label">Выезд</label>
                                        <input type="text" class="form-control" id="checkoutDate"
                                            placeholder="Выберите дату" required>
                                    </div>
                                </div>
                                <div class="form-text">Минимальный срок бронирования: 2 ночи</div>
                            </div>
                            <hr>
                            <div class="mb-4">
                                <h5 class="mb-3"><i class="bi bi-people me-2"></i>Гости</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Взрослые</label>
                                        <div class="input-group">
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="window.changeGuests('adults', -1)"><i
                                                    class="bi bi-dash"></i></button>
                                            <input type="text" class="form-control text-center" id="adultsCount"
                                                value="2" readonly>
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="window.changeGuests('adults', 1)"><i
                                                    class="bi bi-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Дети</label>
                                        <div class="input-group">
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="window.changeGuests('children', -1)"><i
                                                    class="bi bi-dash"></i></button>
                                            <input type="text" class="form-control text-center" id="childrenCount"
                                                value="0" readonly>
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="window.changeGuests('children', 1)"><i
                                                    class="bi bi-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text">Дети до 6 лет размещаются бесплатно</div>
                            </div>
                            <hr>
                            <div class="mb-4">
                                <h5 class="mb-3"><i class="bi bi-person me-2"></i>Контактные данные</h5>
                                <div class="row g-3">
                                    <div class="col-md-6"><label for="guestName" class="form-label">ФИО <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="guestName" 
                                            value="<?php echo htmlspecialchars(($_SESSION['user']['name'] ?? '') . ' ' . ($_SESSION['user']['surname'] ?? '')); ?>" required>
                                    </div>
                                    <div class="col-md-6"><label for="guestEmail" class="form-label">Email <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="guestEmail" 
                                            value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6"><label for="guestPhone" class="form-label">Телефон <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="guestPhone" 
                                            value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6"><label for="guestPassport" class="form-label">Серия и номер
                                            паспорта <span class="text-muted small">(10 цифр)</span></label>
                                            <input type="text" class="form-control" id="guestPassport" 
                                            placeholder="1234 567890" maxlength="11" minlength="11" pattern="\d{4} \d{6}"
                                            value="<?php echo htmlspecialchars($_SESSION['user']['passport'] ?? ''); ?>"
                                            oninput="let v = this.value.replace(/[^0-9]/g, ''); if (v.length > 4) v = v.slice(0,4) + ' ' + v.slice(4,10); this.value = v;">
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-4">
                                <h5 class="mb-3"><i class="bi bi-chat-left-text me-2"></i>Пожелания</h5>
                                <textarea class="form-control" id="guestComments" rows="3"></textarea>
                            </div>
                            <hr>
                            <div class="mb-4">
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="rulesAgree"
                                        required checked><label class="form-check-label" for="rulesAgree">Я согласен с <a
                                            href="privacy.php#rules" target="_blank" class="text-danger fw-medium">правилами бронирования</a></label></div>
                                <div class="form-check mb-4"><input class="form-check-input" type="checkbox"
                                        id="personalDataAgree" required checked><label class="form-check-label small"
                                        for="personalDataAgree">Я согласен на обработку <a href="privacy.php" target="_blank" class="text-danger fw-medium">персональных
                                            данных</a> и принимаю условия <a href="privacy.php" target="_blank" class="text-danger fw-medium">оферты</a></label></div>
                            </div>
                            <div id="bookingError" class="alert alert-danger mb-3" style="display: none; border-radius: 10px; font-size: 0.9rem;">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <span class="error-text"></span>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger btn-lg"><i
                                        class="bi bi-check-circle me-2"></i>Подтвердить бронирование</button>
                                <a href="index.php" class="btn btn-outline-secondary"><i
                                        class="bi bi-arrow-left me-2"></i>Вернуться назад</a>
                            </div>
                        </form>
                    </div>
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
    .legal-sub-item {
        padding-left: 2rem;
        font-size: 0.8125rem;
        color: #555;
    }
    </style>

    <!-- Модальное окно: Правила бронирования -->
    <div class="modal fade" id="rulesModal" tabindex="-1" aria-labelledby="rulesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="modal-title d-none" id="rulesModalLabel">Правила бронирования</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-0 legal-doc-container">
                    <div class="legal-doc-title">Правила и условия бронирования</div>
                    
                    <div class="legal-section-title">1. Общие положения</div>
                    <p class="legal-text">1.1. Настоящие правила (далее — «Правила») определяют порядок взаимодействия между Сервисом BRONIC.RU и Пользователем при осуществлении бронирования объектов временного проживания.</p>
                    <p class="legal-text">1.2. Осуществляя бронирование, Пользователь подтверждает свое полное и безоговорочное согласие с условиями настоящих Правил.</p>
                    
                    <div class="legal-section-title">2. Порядок оплаты</div>
                    <ul class="legal-list">
                        <li class="legal-list-item">2.1. Бронирование считается подтвержденным и гарантированным только после внесения полной (100%) предоплаты стоимости проживания.</li>
                        <li class="legal-list-item">2.2. Оплата производится в рублях РФ через защищенный платежный шлюз ПАО «Сбербанк» или ЮKassa.</li>
                        <li class="legal-list-item">2.3. В итоговую стоимость включены все налоги, сборы и дополнительные услуги, указанные в расчете.</li>
                    </ul>

                    <div class="legal-section-title">3. Условия отмены и изменения бронирования</div>
                    <ul class="legal-list">
                        <li class="legal-list-item">3.1. Бесплатная отмена бронирования возможна не позднее чем за 24 часа до установленного времени заезда (14:00 по местному времени).</li>
                        <li class="legal-list-item">3.2. При отмене менее чем за 24 часа до заезда, с Пользователя удерживается штраф в размере стоимости первой ночи проживания.</li>
                        <li class="legal-list-item">3.3. Возврат денежных средств производится на банковскую карту, с которой была совершена транзакция, в течение 5-10 рабочих дней.</li>
                    </ul>

                    <div class="legal-section-title">4. Правила проживания и ответственность</div>
                    <ul class="legal-list">
                        <li class="legal-list-item">4.1. Время заезда — после 14:00, время выезда — до 12:00.</li>
                        <li class="legal-list-item">4.2. Пользователь обязуется соблюдать правила пожарной безопасности и установленный режим тишины (с 23:00 до 08:00).</li>
                        <li class="legal-list-item">4.3. Курение на территории объектов (включая балконы и лоджии) категорически запрещено.</li>
                    </ul>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-dark px-4" data-bs-dismiss="modal" style="border-radius: 6px;">Закрыть</button>
                    <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal" style="background-color: #fe496a; border-radius: 6px;">Принимаю условия</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно: Персональные данные -->
    <div class="modal fade" id="personalDataModal" tabindex="-1" aria-labelledby="personalDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="modal-title d-none" id="personalDataModalLabel">Персональные данные</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-0 legal-doc-container">
                    <div class="legal-doc-title">Политика в отношении обработки персональных данных</div>
                    
                    <p class="legal-text">Настоящая Политика разработана в соответствии с требованиями Федерального закона от 27.07.2006. №152-ФЗ «О персональных данных» и определяет порядок обработки и меры по обеспечению безопасности персональных данных в Сервисе BRONIC.RU.</p>
                    
                    <div class="legal-section-title">1. Основные понятия</div>
                    <p class="legal-text">1.1. Оператор — администрация Сервиса BRONIC.RU, самостоятельно или совместно с другими лицами организующая обработку персональных данных.</p>
                    <p class="legal-text">1.2. Пользователь — любой посетитель веб-сайта bronic.ru.</p>

                    <div class="legal-section-title">2. Цели обработки персональных данных</div>
                    <ul class="legal-list">
                        <li class="legal-list-item">2.1. Идентификация Пользователя в рамках использования Сервиса.</li>
                        <li class="legal-list-item">2.2. Обеспечение процесса бронирования и оплаты объектов недвижимости.</li>
                        <li class="legal-list-item">2.3. Предоставление Пользователю эффективной клиентской и технической поддержки.</li>
                        <li class="legal-list-item">2.4. Направление уведомлений о статусе бронирования.</li>
                    </ul>

                    <div class="legal-section-title">3. Правовые основания обработки</div>
                    <p class="legal-text">3.1. Оператор обрабатывает персональные данные Пользователя только в случае их заполнения и/или отправки Пользователем самостоятельно через специальные формы на сайте.</p>

                    <div class="legal-section-title">4. Безопасность</div>
                    <p class="legal-text">4.1. Оператор обеспечивает сохранность персональных данных и принимает все возможные меры, исключающие доступ к персональным данным неуполномоченных лиц.</p>
                    <p class="legal-text">4.2. Персональные данные Пользователя никогда, ни при каких условиях не будут переданы третьим лицам, за исключением случаев, связанных с исполнением действующего законодательства.</p>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 6px;">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'inc/_footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="main.js"></script>
    <script>
        $(document).ready(function () {
            const urlParams = new URLSearchParams(window.location.search);
            const propertyName = decodeURIComponent(urlParams.get('name') || '');
            const propertyPrice = parseFloat(urlParams.get('price')) || 2500;
            const propertyLocation = decodeURIComponent(urlParams.get('location') || '');

            if (propertyName) $('#propertyTitle').text(propertyName);
            $('#propertyLocation').text(propertyLocation);
            $('#pricePerNight').text(propertyPrice.toLocaleString('ru-RU') + ' ₽');

            const resourceId = urlParams.get('id');
            if (resourceId) {
                $.getJSON('http://' + (window.location.hostname || 'localhost') + ':8000/resources/' + resourceId, function(data) {
                    if (data.image_url) $('#propertyImage').attr('src', data.image_url);
                    if (data.area) $('#detailArea').text(data.area);
                    if (data.guests) $('#detailGuests').text(data.guests);
                    if (data.bedrooms) $('#detailBedrooms').text(data.bedrooms);
                });
            }

            function formatDate(date) {
                let d = date.getDate(), m = date.getMonth() + 1, y = date.getFullYear();
                return `${d < 10 ? '0' + d : d}.${m < 10 ? '0' + m : m}.${y}`;
            }

            let checkinStr = urlParams.get('checkin'), checkoutStr = urlParams.get('checkout');
            let today = new Date();
            let checkinDate = today;
            if (checkinStr) {
                let p = checkinStr.split('.');
                if (p.length === 3) checkinDate = new Date(p[2], p[1] - 1, p[0]);
            }
            if (checkinDate < today) checkinDate = today;

            let tomorrow = new Date(checkinDate);
            tomorrow.setDate(checkinDate.getDate() + 2);
            let checkoutDate = tomorrow;
            if (checkoutStr) {
                let p = checkoutStr.split('.');
                if (p.length === 3) {
                    let pot = new Date(p[2], p[1] - 1, p[0]);
                    if (pot >= tomorrow) checkoutDate = pot;
                }
            }

            $('#checkinDate').val(formatDate(checkinDate)).datepicker({
                dateFormat: "dd.mm.yy", minDate: 0,
                onSelect: function (selected) {
                    let min = $.datepicker.parseDate('dd.mm.yy', selected);
                    min.setDate(min.getDate() + 2);
                    $('#checkoutDate').datepicker('option', 'minDate', min);
                    calculatePrice();
                }
            });

            $('#checkoutDate').val(formatDate(checkoutDate)).datepicker({
                dateFormat: "dd.mm.yy", minDate: 2,
                onSelect: calculatePrice
            });

            let currentTotal = 0;
            function calculatePrice() {
                let ci = $('#checkinDate').datepicker('getDate'), co = $('#checkoutDate').datepicker('getDate');
                if (ci && co) {
                    let nights = Math.ceil((co - ci) / (86400000));
                    $('#nightsCount').text(nights);
                    let sub = nights * propertyPrice;
                    currentTotal = sub + 1000 + 750;
                    $('#subtotal').text(sub.toLocaleString('ru-RU') + ' ₽');
                    $('#totalPrice').text(currentTotal.toLocaleString('ru-RU') + ' ₽');
                }
            }
            calculatePrice();

            function showError(msg) {
                $('#bookingError .error-text').text(msg);
                $('#bookingError').fadeIn();
                // Прокрутка к ошибке
                $('html, body').animate({
                    scrollTop: $("#bookingError").offset().top - 200
                }, 500);
            }

            function hideError() {
                $('#bookingError').hide();
            }

            $('#bookingForm').on('submit', function (e) {
                e.preventDefault();
                hideError();
                const sessionUserId = <?php echo $_SESSION['user']['id'] ?? 0; ?>;

                if (!sessionUserId) {
                    showError('Пожалуйста, войдите в аккаунт для завершения бронирования');
                    setTimeout(() => window.location.href = 'login.php', 2000);
                    return;
                }

                let submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Обработка...');

                let bookingData = {
                    property: $('#propertyTitle').text(),
                    location: $('#propertyLocation').text(),
                    price_per_night: propertyPrice,
                    checkin: $('#checkinDate').val(),
                    checkout: $('#checkoutDate').val(),
                    nights: $('#nightsCount').text(),
                    adults: $('#adultsCount').val(),
                    children: $('#childrenCount').val(),
                    name: $('#guestName').val(),
                    email: $('#guestEmail').val(),
                    phone: $('#guestPhone').val(),
                    passport: $('#guestPassport').val(),
                    comment: $('#guestComments').val(), // Исправлено на comment для бекенда
                    total: currentTotal,
                    resource_id: resourceId || 1,
                    user_id: sessionUserId,
                    start_time: $('#checkinDate').val(),
                    end_time: $('#checkoutDate').val(),
                    price: currentTotal
                };

                $.ajax({
                    url: 'http://' + (window.location.hostname || 'localhost') + ':8000/bookings',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(bookingData),
                    success: function(response) {
                        if (response.success || response.id) {
                            $.ajax({
                                url: 'http://' + (window.location.hostname || 'localhost') + ':8000/payments/create',
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    booking_id: response.id,
                                    amount: currentTotal
                                }),
                                success: function(payRes) {
                                    submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Подтвердить бронирование');
                                    if (payRes.confirmation_url) {
                                        window.location.href = payRes.confirmation_url;
                                    } else if (payRes.error) {
                                        showError('Ошибка платежной системы: ' + payRes.error);
                                        setTimeout(() => window.location.href = 'bookings.php', 3000);
                                    } else {
                                        window.location.href = 'bookings.php';
                                    }
                                },
                                error: function() {
                                    submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Подтвердить бронирование');
                                    window.location.href = 'bookings.php';
                                }
                            });
                        } else {
                            submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Подтвердить бронирование');
                            showError(response.error || 'Не удалось сохранить бронирование. Попробуйте позже.');
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Подтвердить бронирование');
                        showError('Произошла ошибка при отправке запроса. Проверьте соединение с интернетом.');
                        console.error(xhr.responseText);
                    }
                });
            });
        });

        window.changeGuests = function(type, delta) {
            let input = $(type === 'adults' ? '#adultsCount' : '#childrenCount');
            let val = parseInt(input.val()) + delta;
            if (val < (type === 'adults' ? 1 : 0)) val = (type === 'adults' ? 1 : 0);
            if (val > 10) val = 10;
            input.val(val);
        };
    </script>
</body>
</html>
