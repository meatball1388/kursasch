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
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="icon" href="../img/bronic.png" type="image/png">
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
                                        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
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

    <!-- ИИ-рекомендации -->
    <div class="container mt-4"><hr class="my-4"></div>
    <div class="container mt-4 mb-5">
        <div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="bi bi-robot text-danger me-2"></i>ИИ подобрал для вас</h3>
                        <p class="text-muted mb-0">Рекомендации на основе машинного обучения (Random Forest)</p>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-light text-muted" id="aiStatusBadge">
                            <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Проверка...
                        </span>
                        <button class="btn btn-outline-danger btn-sm" id="aiTrainBtn" style="border-radius:10px; display:none;">
                            <i class="bi bi-gear me-1"></i>Обучить модель
                        </button>
                    </div>
                </div>

                <!-- Форма параметров для рекомендаций -->
                <div class="row g-3 mb-4" id="aiParamsForm">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Город</label>
                        <select class="form-select" id="aiCity">
                            <option value="Москва">Москва</option>
                            <option value="Санкт-Петербург">Санкт-Петербург</option>
                            <option value="Казань">Казань</option>
                            <option value="Сочи">Сочи</option>
                            <option value="Екатеринбург">Екатеринбург</option>
                            <option value="Новосибирск">Новосибирск</option>
                            <option value="Краснодар">Краснодар</option>
                            <option value="Нижний Новгород">Нижний Новгород</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Тип жилья</label>
                        <select class="form-select" id="aiType">
                            <option value="apartment">Квартира</option>
                            <option value="house">Дом</option>
                            <option value="studio">Студия</option>
                            <option value="room">Комната</option>
                            <option value="villa">Вилла</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Бюджет, ₽</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="aiMinPrice" value="2000" placeholder="от">
                            <input type="number" class="form-control" id="aiMaxPrice" value="10000" placeholder="до">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small text-muted">Гости</label>
                        <input type="number" class="form-control" id="aiGuests" value="2" min="1" max="10">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">&nbsp;</label>
                        <button class="btn btn-danger w-100" id="aiRecommendBtn" style="border-radius:10px;">
                            <i class="bi bi-stars me-1"></i>Подобрать
                        </button>
                    </div>
                </div>

                <!-- Результаты -->
                <div id="aiResults">
                    <div class="text-center py-4 text-muted" id="aiPlaceholder">
                        <i class="bi bi-lightbulb" style="font-size:2rem;"></i>
                        <p class="mt-2 mb-0">Нажмите «Подобрать», чтобы получить персональные рекомендации</p>
                    </div>
                    <div class="text-center py-4 d-none" id="aiLoading">
                        <div class="spinner-border text-danger"></div>
                        <p class="mt-2 text-muted">ИИ анализирует данные...</p>
                    </div>
                    <div class="row g-3 d-none" id="aiCards"></div>
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
            // ========== ИИ-РЕКОМЕНДАЦИИ ==========
            var API = 'http://' + (window.location.hostname || 'localhost') + ':8000';

            // Проверяем статус модели
            function checkAiStatus() {
                $.getJSON(API + '/ai/status', function(res) {
                    if (res.status === 'ready') {
                        $('#aiStatusBadge').removeClass('text-muted').addClass('text-success')
                            .html('<i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Модель готова');
                        $('#aiTrainBtn').hide();
                    } else {
                        $('#aiStatusBadge').removeClass('text-muted').addClass('text-warning')
                            .html('<i class="bi bi-exclamation-circle me-1"></i>Не обучена');
                        $('#aiTrainBtn').show();
                    }
                }).fail(function() {
                    $('#aiStatusBadge').html('<i class="bi bi-x-circle me-1"></i>Сервер недоступен');
                });
            }
            checkAiStatus();

            // Обучить модель
            $('#aiTrainBtn').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Обучение...');
                $.ajax({
                    url: API + '/ai/train',
                    method: 'POST',
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Обучена!');
                        $('#aiStatusBadge').removeClass('text-warning').addClass('text-success')
                            .html('<i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Модель готова');
                        setTimeout(function() { btn.hide(); }, 2000);
                        // Показываем метрики
                        if (res.metrics) {
                            alert('Модель обучена!\n\nТочность: ' + (res.metrics.accuracy * 100).toFixed(1) + '%\nПолнота: ' + (res.metrics.recall * 100).toFixed(1) + '%');
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="bi bi-gear me-1"></i>Обучить модель');
                        alert('Ошибка обучения модели');
                    }
                });
            });

            // Получить рекомендации
            $('#aiRecommendBtn').on('click', function() {
                var today = new Date();
                var nextWeek = new Date(today);
                nextWeek.setDate(today.getDate() + 7);

                var body = {
                    city: $('#aiCity').val(),
                    property_type: $('#aiType').val(),
                    min_price: parseInt($('#aiMinPrice').val()) || 0,
                    max_price: parseInt($('#aiMaxPrice').val()) || 20000,
                    rooms: 2,
                    amenities: ["wifi", "kitchen"],
                    check_in: today.toISOString().split('T')[0],
                    check_out: nextWeek.toISOString().split('T')[0],
                    guests: parseInt($('#aiGuests').val()) || 2,
                    top_n: 5
                };

                $('#aiPlaceholder').addClass('d-none');
                $('#aiCards').addClass('d-none').empty();
                $('#aiLoading').removeClass('d-none');

                $.ajax({
                    url: API + '/ai/recommend',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(body),
                    success: function(res) {
                        $('#aiLoading').addClass('d-none');
                        if (!res.recommendations || res.recommendations.length === 0) {
                            $('#aiPlaceholder').removeClass('d-none')
                                .html('<i class="bi bi-emoji-neutral" style="font-size:2rem;"></i><p class="mt-2 mb-0">Рекомендации не найдены. Попробуйте другие параметры.</p>');
                            return;
                        }

                        // Загружаем данные по каждому рекомендованному объекту
                        var loadedCount = 0;
                        var totalToLoad = res.recommendations.length;

                        $.each(res.recommendations, function(idx, rec) {
                            $.getJSON(API + '/resources/' + rec.property_id, function(item) {
                                var scorePct = Math.round(rec.score * 100);
                                var scoreColor = scorePct >= 70 ? 'success' : scorePct >= 40 ? 'warning' : 'secondary';
                                var name = $('<div>').text(item.name || 'Объект #' + rec.property_id).html();
                                var address = $('<div>').text(item.address || item.location || '').html();
                                var price = Number(item.base_price || 0).toLocaleString('ru-RU');
                                var imgUrl = item.image_url || '../img/property/metro-plus.png';

                                var card = `
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card border-0 shadow-sm h-100" style="border-radius:14px; cursor:pointer; transition:0.3s;" 
                                             onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'"
                                             onclick="window.location='property.php?id=${rec.property_id}'">
                                            <div class="position-relative">
                                                <img src="${imgUrl}" class="card-img-top" alt="${name}" 
                                                     style="height:180px; object-fit:cover; border-radius:14px 14px 0 0;"
                                                     onerror="this.src='../img/property/metro-plus.png'">
                                                <span class="badge bg-${scoreColor} position-absolute top-0 end-0 m-2" style="font-size:0.85rem;">
                                                    <i class="bi bi-cpu me-1"></i>${scorePct}% совпадение
                                                </span>
                                            </div>
                                            <div class="card-body p-3">
                                                <h6 class="fw-bold mb-1">${name}</h6>
                                                <p class="text-muted small mb-2"><i class="bi bi-geo-alt text-danger me-1"></i>${address}</p>
                                                <div class="fw-bold text-danger">${price} ₽ <span class="text-muted fw-normal small">/ сутки</span></div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                $('#aiCards').append(card);
                            }).fail(function() {
                                // Объект не найден в БД — показываем ID
                                var scorePct = Math.round(rec.score * 100);
                                var card = `
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card border-0 bg-light h-100" style="border-radius:14px;">
                                            <div class="card-body p-3 text-center text-muted">
                                                <i class="bi bi-building" style="font-size:2rem;"></i>
                                                <p class="mt-2 mb-1 fw-medium">Объект #${rec.property_id}</p>
                                                <span class="badge bg-secondary">${scorePct}%</span>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                $('#aiCards').append(card);
                            }).always(function() {
                                loadedCount++;
                                if (loadedCount >= totalToLoad) {
                                    $('#aiCards').removeClass('d-none');
                                }
                            });
                        });
                    },
                    error: function(xhr) {
                        $('#aiLoading').addClass('d-none');
                        var msg = 'Ошибка';
                        try { msg = JSON.parse(xhr.responseText).detail || msg; } catch(e) {}
                        $('#aiPlaceholder').removeClass('d-none')
                            .html('<i class="bi bi-exclamation-triangle text-warning" style="font-size:2rem;"></i><p class="mt-2 mb-0">' + msg + '</p>');
                    }
                });
            });
        });
    </script>
</body>

</html>
