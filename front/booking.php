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
                        <div class="d-flex gap-3 mb-3">
                            <span class="badge bg-light text-dark"><i class="bi bi-people me-1"></i>2 гостя</span>
                            <span class="badge bg-light text-dark"><i class="bi bi-door-open me-1"></i>1 спальня</span>
                            <span class="badge bg-light text-dark"><i class="bi bi-aspect-ratio me-1"></i>45 м²</span>
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
                                                class="text-danger">*</span></label><input type="text"
                                            class="form-control" id="guestName" required></div>
                                    <div class="col-md-6"><label for="guestEmail" class="form-label">Email <span
                                                class="text-danger">*</span></label><input type="email"
                                            class="form-control" id="guestEmail" required></div>
                                    <div class="col-md-6"><label for="guestPhone" class="form-label">Телефон <span
                                                class="text-danger">*</span></label><input type="text"
                                            class="form-control" id="guestPhone" required></div>
                                    <div class="col-md-6"><label for="guestPassport" class="form-label">Серия и номер
                                            паспорта</label><input type="text" class="form-control" id="guestPassport">
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
                                        required><label class="form-check-label" for="rulesAgree">Я согласен с <a
                                            href="javascript:void(0)" onclick="alert('Правила бронирования: \n1. Оплата производится сразу. \n2. Бесплатная отмена за 24 часа. \n3. Соблюдайте тишину.')">правилами бронирования</a></label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox"
                                        id="personalDataAgree" required><label class="form-check-label"
                                        for="personalDataAgree">Я согласен на обработку <a href="javascript:void(0)" onclick="alert('Мы обрабатываем ваши данные только для оформления бронирования и не передаем их третьим лицам.')">персональных
                                            данных</a></label></div>
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
    <?php include 'inc/_footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="main.js"></script>
    <script>
        $(document).ready(function () {
            // Получаем параметры из URL
            const urlParams = new URLSearchParams(window.location.search);
            const propertyName = decodeURIComponent(urlParams.get('name') || '');
            const propertyPrice = parseFloat(urlParams.get('price')) || 2500;
            const propertyLocation = decodeURIComponent(urlParams.get('location') || 'Москва, ул. Тверская, д. 15');

            if (propertyName) $('#propertyTitle').text(propertyName);
            $('#propertyLocation').text(propertyLocation);
            $('#pricePerNight').text(propertyPrice.toLocaleString('ru-RU') + ' ₽');

            // Загружаем реальное фото объекта
            const resourceId = urlParams.get('id');
            if (resourceId) {
                $.getJSON('http://' + (window.location.hostname || 'localhost') + ':8000/resources/' + resourceId, function(data) {
                    if (data.image_url) {
                        $('#propertyImage').attr('src', data.image_url);
                    }
                });
            }

            function formatDate(date) {
                let d = date.getDate(),
                    m = date.getMonth() + 1,
                    y = date.getFullYear();
                return `${d < 10 ? '0' + d : d}.${m < 10 ? '0' + m : m}.${y}`;
            }

            // Синхронизация дат из URL
            let checkinStr = urlParams.get('checkin');
            let checkoutStr = urlParams.get('checkout');
            let adults = urlParams.get('adults');
            let children = urlParams.get('children');

            if (adults) {
                $('#adultsCount').val(adults);
                window.adults = parseInt(adults);
            }
            if (children) {
                $('#childrenCount').val(children);
                window.children = parseInt(children);
            }

            let today = new Date();
            let checkinDate = today;
            if (checkinStr) {
                let parts = checkinStr.split('.');
                if (parts.length === 3) checkinDate = new Date(parts[2], parts[1] - 1, parts[0]);
            }
            if (checkinDate < today) checkinDate = today;

            let tomorrow = new Date(checkinDate);
            tomorrow.setDate(checkinDate.getDate() + 2); // Минимум 2 ночи
            
            let checkoutDate = tomorrow;
            if (checkoutStr) {
                let parts = checkoutStr.split('.');
                if (parts.length === 3) {
                    let potentialCheckout = new Date(parts[2], parts[1] - 1, parts[0]);
                    if (potentialCheckout >= tomorrow) checkoutDate = potentialCheckout;
                }
            }

            $('#checkinDate').val(formatDate(checkinDate)).datepicker({
                dateFormat: "dd.mm.yy",
                minDate: 0,
                onSelect: function (selected) {
                    let min = $.datepicker.parseDate('dd.mm.yy', selected);
                    min.setDate(min.getDate() + 2);
                    $('#checkoutDate').datepicker('option', 'minDate', min);
                    let currentCheckout = $('#checkoutDate').datepicker('getDate');
                    if (!currentCheckout || currentCheckout < min) {
                        $('#checkoutDate').val(formatDate(min));
                    }
                    calculatePrice();
                }
            });

            $('#checkoutDate').val(formatDate(checkoutDate)).datepicker({
                dateFormat: "dd.mm.yy",
                minDate: 2,
                onSelect: calculatePrice
            });
            // Обновим minDate для checkout на основе начального checkin
            let initialMinCheckout = new Date(checkinDate);
            initialMinCheckout.setDate(checkinDate.getDate() + 2);
            $('#checkoutDate').datepicker('option', 'minDate', initialMinCheckout);

            let currentTotal = 0;
            function calculatePrice() {
                let checkin = $('#checkinDate').datepicker('getDate'),
                    checkout = $('#checkoutDate').datepicker('getDate');
                if (checkin && checkout) {
                    let nights = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
                    $('#nightsCount').text(nights);
                    let subtotal = nights * propertyPrice;
                    currentTotal = subtotal + 1000 + 750;
                    $('#subtotal').text(subtotal.toLocaleString('ru-RU') + ' ₽');
                    $('#totalPrice').text(currentTotal.toLocaleString('ru-RU') + ' ₽');
                }
            }
            calculatePrice();

            $('#bookingForm').on('submit', function (e) {
                e.preventDefault();
                
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
                    comments: $('#guestComments').val(),
                    total: currentTotal,
                    resource_id: urlParams.get('id') || 1 // Передаем ID ресурса
                };

                const userSessionId = "<?php echo isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : ''; ?>";
                if (!userSessionId) {
                    alert('Пожалуйста, войдите в аккаунт');
                    window.location.href = 'login.php';
                    return;
                }
                
                bookingData.user_id = parseInt(userSessionId);
                
                // 2. Создаем бронирование
                $.ajax({
                    url: 'http://' + (window.location.hostname || 'localhost') + ':8000/bookings',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(bookingData),
                    success: function(response) {
                        if (response.success || response.id) {
                            // 3. Создаем платеж
                            $.ajax({
                                url: 'http://' + (window.location.hostname || 'localhost') + ':8000/payments/create',
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    booking_id: response.id,
                                    amount: bookingData.total
                                }),
                                success: function(payRes) {
                                    submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Подтвердить бронирование');
                                    if (payRes.confirmation_url) {
                                        window.location.href = payRes.confirmation_url;
                                    } else if (payRes.error) {
                                        alert('Ошибка ЮKassa: ' + payRes.error);
                                        window.location.href = 'bookings.php';
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
                            alert('Ошибка: ' + (response.error || 'Не удалось сохранить бронирование'));
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Подтвердить бронирование');
                        alert('Произошла ошибка при отправке запроса на сервер.');
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
</body>

</html>
