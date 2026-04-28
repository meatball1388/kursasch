<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // print_r($_SESSION);
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRONIC.RU - Бронирование жилья</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <?php include 'inc/_nav.php'; ?>

    <!-- Главный экран с поиском -->
    <section class="face" id="face_bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <h1 class="mb-3">Забронируй жилье быстро, а главное безопасно!</h1>
                    <p class="mb-4">Лучший сервис по всей России.</p>
                    <form id="searchForm" action="filter.php" method="GET">
                        <div class="row g-2 align-items-center justify-content-center">
                            <div class="col-md-3">
                                <div class="dropdown w-100">
                                    <button
                                        class="btn btn-light border form-control form-control-lg text-start d-flex align-items-center justify-content-between"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <div class="text-start">
                                            <div class="fw-medium"><i class="bi bi-geo-alt me-1"
                                                    style="color: #fe496a;"></i><span id="selectedCity">Выберите
                                                    город</span></div>
                                            <div class="small text-muted" id="cityHint">Например: Москва</div>
                                        </div>
                                        <i class="bi bi-chevron-down text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu w-100 p-2"
                                        style="min-width: 300px; max-height: 300px; overflow-y: auto;">
                                        <li class="px-2 pb-2"><input type="text" class="form-control form-control-sm"
                                                id="citySearch" placeholder="🔍 Поиск города..."></li>
                                        <li>
                                            <hr class="dropdown-divider my-1">
                                        </li>
                                        <div id="cityList"></div>
                                    </ul>
                                    <input type="hidden" name="city" id="citySelector">
                                </div>
                            </div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-lg"
                                    placeholder="Заезд" name="checkin" id="checkinDate"></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-lg"
                                    placeholder="Отъезд" name="checkout" id="checkoutDate"></div>
                            <div class="col-md-2">
                                <div class="dropdown">
                                    <button class="btn btn-light border form-control form-control-lg text-start"
                                        type="button" data-bs-toggle="dropdown">
                                        <div class="fw-medium">Гости</div>
                                        <div class="small text-muted" id="guestsSummary">2 взрослых без детей</div>
                                    </button>
                                    <ul class="dropdown-menu w-100 p-3" style="min-width: 300px;">
                                        <li class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-medium">Взрослые</div><small class="text-muted">от 14
                                                        лет</small>
                                                </div>
                                                <div class="d-flex align-items-center gap-2"><button
                                                        class="btn btn-sm btn-outline-secondary" type="button"
                                                        onclick="changeGuests('adults', -1)"
                                                        style="width:32px;height:32px;">−</button><span
                                                        class="fw-medium" id="adultsCount"
                                                        style="min-width:24px;text-align:center;">2</span><button
                                                        class="btn btn-sm btn-outline-secondary" type="button"
                                                        onclick="changeGuests('adults', 1)"
                                                        style="width:32px;height:32px;">+</button></div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-medium">Дети</div><small class="text-muted">до 14
                                                        лет</small>
                                                </div>
                                                <div class="d-flex align-items-center gap-2"><button
                                                        class="btn btn-sm btn-outline-secondary" type="button"
                                                        onclick="changeGuests('children', -1)"
                                                        style="width:32px;height:32px;">−</button><span
                                                        class="fw-medium" id="childrenCount"
                                                        style="min-width:24px;text-align:center;">0</span><button
                                                        class="btn btn-sm btn-outline-secondary" type="button"
                                                        onclick="changeGuests('children', 1)"
                                                        style="width:32px;height:32px;">+</button></div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-3"><button type="submit" class="btn btn-danger btn-lg w-100">Смотреть
                                    цены</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Контейнер для динамических карточек -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-3"><?php include 'inc/_aside.php'; ?></div>
            <div class="col-lg-9">
                <h2 class="mb-4">Доступные варианты</h2>
                <div id="searchResults" class="row">
                    <div class="col-12 text-center py-5" id="loadingSpinner">
                        <div class="spinner-border text-danger" style="width:3rem;height:3rem;"></div>
                        <p class="mt-3 text-muted">Загрузка...</p>
                    </div>
                </div>
                <div id="noResults" class="alert alert-info d-none"><i class="bi bi-info-circle me-2"></i>Нет доступных
                    объектов</div>
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
            // helper – читаем избранное
            function getFavorites() {
                try {
                    return JSON.parse(localStorage.getItem('bronic_favorites') || '[]');
                } catch (e) {
                    return [];
                }
            }
            // Загружаем все объекты для главной страницы
            function loadAllProperties() {
                $.ajax({
                    url: 'http://localhost:8000/search',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({}), // пустой объект – вернёт все активные ресурсы
                    success: function (response) {
                        $('#loadingSpinner').hide();
                        if (!response.results || response.results.length === 0) {
                            $('#noResults').removeClass('d-none');
                            return;
                        }
                        $('#searchResults').empty();
                        var typeNames = {
                            'appartment': 'Квартира',
                            'dacha': 'Дача',
                            'room': 'Комната',
                            'cottedzh': 'Коттедж'
                        };
                        
                        $.each(response.results, function (index, item) {

                            var typeName = typeNames[item.type] || 'Недвижимость';
                            var priceFormatted = Number(item.base_price).toLocaleString('ru-RU');
                            var name = $('<div>').text(item.name || 'Без названия').html();
                            var address = $('<div>').text(item.address || item.location ||
                                'Адрес не указан').html();
                            var description = $('<div>').text(item.description ||
                                'Описание отсутствует').html();
                            var isFav = getFavorites && getFavorites().some(f => f.id == item
                                .id);
                            var heartClass = isFav ? 'bi-heart-fill text-danger' : 'bi-heart';
                            var itemJson = JSON.stringify(item).replace(/"/g, '&quot;');
                            var cardHtml = `
                                <div class="col-12 mb-4 property-item" data-id="${item.id}" data-type="${item.type}" data-price="${item.base_price}">
                                    <div class="property-card card border-0 shadow-sm">
                                        <div class="row g-0">
                                            <div class="col-md-4 position-relative">
                                                <img src="${item.image_url || './img/property/room_example.png'}" class="img-fluid rounded-start h-100 w-100 object-fit-cover" alt="${name.replace(/"/g, '&quot;')}" style="min-height: 200px;">
                                                <button class="btn btn-favorite position-absolute top-0 end-0 m-3 border-0" title="Добавить в избранное" data-item="${itemJson}"><i class="bi bi-heart ${heartClass}"></i></button>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="card-body p-4">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div><h5 class="card-title mb-1 fw-bold">${name}</h5><p class="card-text text-muted mb-0"><i class="bi bi-geo-alt-fill text-danger me-1"></i>${address}</p></div>
                                                        <div class="text-end"><div class="fw-bold"><i class="bi bi-star-fill text-warning me-1"></i>4.5</div><small class="text-muted">(0 отзывов)</small></div>
                                                    </div>
                                                    <hr>
                                                    <p class="card-text text-muted mb-3">${description}</p>
                                                    <div class="mb-3"><span class="badge bg-light text-dark me-1"><i class="bi bi-tag me-1"></i>${typeName}</span></div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="fw-bold fs-4 text-danger">${priceFormatted} ₽ <span class="text-muted fs-6 fw-normal">/ сутки</span></div>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-outline-primary btn-show-phone" data-phone-visible="false"><i class="bi bi-telephone me-1"></i>Показать телефон</button>
                                                            <button class="btn btn-danger btn-book" data-id="${item.id}" data-name="${name}" data-price="${item.base_price}" data-location="${address}">Забронировать <i class="bi bi-arrow-right ms-1"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('#searchResults').append(cardHtml);
                        });
                    },
                    error: function () {
                        $('#loadingSpinner').hide();
                        $('#searchResults').html(
                            '<div class="col-12"><div class="alert alert-danger">Ошибка загрузки данных</div></div>'
                        );
                    }
                });
            }
            loadAllProperties();
        });
    </script>
</body>

</html>