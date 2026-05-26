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
    <title>Сдать жильё - BRONIC.RU</title>

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

<body>

    <!-- Навигация -->
    <?php include 'inc/_nav.php'; ?>

    <!-- Заголовок страницы -->
    <section class="bg-light py-5 border-bottom">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="mb-3">
                        <i class="bi bi-house-door me-2" style="color: #fe496a;"></i>
                        Сдать жильё
                    </h1>
                    <p class="text-muted">Разместите своё объявление и начните принимать гостей уже сегодня</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Основной контент -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Форма размещения жилья -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4 fw-bold">Разместить объявление</h5>
                        
                        <form id="rentForm">
                            <!-- Тип жилья -->
                            <div class="mb-4">
                                <label class="form-label fw-medium">Тип жилья</label>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <input type="radio" class="btn-check" name="property_type" id="apt" value="apartment" checked>
                                        <label class="btn btn-outline-secondary w-100 py-3" for="apt">
                                            <i class="bi bi-building d-block fs-4 mb-1"></i>
                                            <small>Квартира</small>
                                        </label>
                                    </div>
                                    <div class="col-4">
                                        <input type="radio" class="btn-check" name="property_type" id="house" value="house">
                                        <label class="btn btn-outline-secondary w-100 py-3" for="house">
                                            <i class="bi bi-house d-block fs-4 mb-1"></i>
                                            <small>Дом</small>
                                        </label>
                                    </div>
                                    <div class="col-4">
                                        <input type="radio" class="btn-check" name="property_type" id="room" value="room">
                                        <label class="btn btn-outline-secondary w-100 py-3" for="room">
                                            <i class="bi bi-door-open d-block fs-4 mb-1"></i>
                                            <small>Комната</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Адрес -->
                            <div class="mb-3">
                                <label for="address" class="form-label fw-medium">Адрес</label>
                                <input type="text" class="form-control form-control-lg" id="address" name="address" 
                                    placeholder="Город, улица, дом, квартира" required>
                            </div>

                            <!-- Описание -->
                            <div class="mb-3">
                                <label for="description" class="form-label fw-medium">Описание</label>
                                <textarea class="form-control" id="description" name="description" rows="4" 
                                    placeholder="Расскажите о вашем жилье: что есть рядом, особенности, преимущества..." required></textarea>
                            </div>

                            <!-- Параметры жилья -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="guests" class="form-label fw-medium">Гостей</label>
                                    <input type="number" class="form-control" id="guests" name="guests" min="1" max="20" value="2" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="bedrooms" class="form-label fw-medium">Спален</label>
                                    <input type="number" class="form-control" id="bedrooms" name="bedrooms" min="1" max="10" value="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="beds" class="form-label fw-medium">Кроватей</label>
                                    <input type="number" class="form-control" id="beds" name="beds" min="1" max="20" value="1" required>
                                </div>
                            </div>

                            <!-- Цена -->
                            <div class="mb-3">
                                <label for="price" class="form-label fw-medium">Цена за ночь (₽)</label>
                                <input type="number" class="form-control form-control-lg" id="price" name="price" 
                                    placeholder="2500" min="500" required>
                            </div>

                            <!-- Удобства -->
                            <div class="mb-4">
                                <label class="form-label fw-medium d-block">Удобства</label>
                                <div class="row g-2">
                                    <div class="col-6 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="wifi" id="wifi">
                                            <label class="form-check-label" for="wifi">
                                                <i class="bi bi-wifi me-1"></i>Wi-Fi
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="kitchen" id="kitchen">
                                            <label class="form-check-label" for="kitchen">
                                                <i class="bi bi-cup-hot me-1"></i>Кухня
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="parking" id="parking">
                                            <label class="form-check-label" for="parking">
                                                <i class="bi bi-car-front me-1"></i>Парковка
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="tv" id="tv">
                                            <label class="form-check-label" for="tv">
                                                <i class="bi bi-tv me-1"></i>ТВ
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="washer" id="washer">
                                            <label class="form-check-label" for="washer">
                                                <i class="bi bi-lightning-charge me-1"></i>Стиральная машина
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="ac" id="ac">
                                            <label class="form-check-label" for="ac">
                                                <i class="bi bi-snow me-1"></i>Кондиционер
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Фотографии -->
                            <div class="mb-4">
                                <label for="photos" class="form-label fw-medium">Фотографии</label>
                                <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*" required>
                                <small class="text-muted">Загрузите минимум 3 фотографии. Поддерживаются форматы: JPG, PNG, WEBP, GIF.</small>
                            </div>

                            <!-- Контакты -->
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-medium">Телефон для связи</label>
                                <input type="tel" class="form-control form-control-lg" id="phone" name="phone" 
                                    placeholder="+7 (___) ___-__-__" required>
                            </div>

                            <!-- Кнопка отправки -->
                            <button type="submit" class="btn btn-danger btn-lg w-100 py-3" 
                                style="background-color: #fe496a; border: none;">
                                <i class="bi bi-send me-2"></i>Разместить объявление
                            </button>

                            <p class="small text-muted text-center mt-3 mb-0">
                                После отправки ваше объявление пройдет модерацию в течение 24 часов
                            </p>
                        </form>
                    </div>
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
        $(document).ready(function() {
            $('#rentForm').on('submit', function(e) {
                e.preventDefault();

                var $btn = $(this).find('button[type="submit"]');
                var originalText = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Отправка...');

                var propertyType = $('input[name="property_type"]:checked').val() || 'apartment';
                var address = $('#address').val() || '';
                var description = $('#description').val() || '';
                var guests = parseInt($('#guests').val()) || 1;
                var bedrooms = parseInt($('#bedrooms').val()) || 1;
                var beds = parseInt($('#beds').val()) || 1;
                var price = parseFloat($('#price').val()) || 0;
                var phone = $('#phone').val() || '';

                var amenities = [];
                $('input[name="amenities[]"]:checked').each(function() {
                    amenities.push($(this).val());
                });

                var locationParts = address.split(',');
                var location = locationParts.length > 0 ? $.trim(locationParts[0]) : '';

                var typeNames = {
                    'apartment': 'Квартира',
                    'house': 'Дом',
                    'room': 'Комната'
                };
                var typeName = typeNames[propertyType] || 'Объект';

                var apiData = {
                    name: typeName + ' по адресу ' + address,
                    type: propertyType,
                    description: description,
                    base_price: price,
                    is_active: true,
                    address: address,
                    location: location,
                    details: {
                        guests: guests,
                        bedrooms: bedrooms,
                        beds: beds,
                        amenities: amenities,
                        phone: phone
                    }
                };

                $.ajax({
                    url: 'http://' + (window.location.hostname || 'localhost') + ':8000/resources',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(apiData),
                    success: function(response) {
                        alert('Ваше объявление успешно размещено и отправлено на модерацию!');
                        window.location.href = 'index.php';
                    },
                    error: function(xhr) {
                        var errorMsg = 'Ошибка публикации';
                        try {
                            var resp = xhr.responseJSON;
                            errorMsg = resp ? resp.error || resp.message || errorMsg : errorMsg;
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
