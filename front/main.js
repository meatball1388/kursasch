// ========== ГЛОБАЛЬНЫЕ ФУНКЦИИ И ПЕРЕМЕННЫЕ (доступны для onclick) ==========
window.adults = 2;
window.children = 0;

window.showToast = function(msg, type = 'danger') {
    let $container = $('#toastContainer');
    if ($container.length === 0) {
        $('body').append('<div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>');
        $container = $('#toastContainer');
    }

    const id = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : (type === 'warning' ? 'bg-warning text-dark' : 'bg-danger');
    const icon = type === 'success' ? 'bi-check-circle' : (type === 'warning' ? 'bi-exclamation-circle' : 'bi-exclamation-triangle');

    const toastHtml = `
        <div id="${id}" class="toast align-items-center text-white ${bgClass} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              <i class="bi ${icon} me-2"></i> ${msg}
            </div>
            <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
    `;

    $container.append(toastHtml);
    const $toast = $('#' + id);
    const bsToast = new bootstrap.Toast($toast[0], { delay: 5000 });
    bsToast.show();

    $toast.on('hidden.bs.toast', function () {
        $(this).remove();
    });
};

window.changeGuests = function (type, delta) {
    if (type === 'adults') {
        window.adults = Math.max(1, Math.min(10, window.adults + delta));
        updateGuestElement("#adultsCount", window.adults);
    } else if (type === 'children') {
        window.children = Math.max(0, Math.min(10, window.children + delta));
        updateGuestElement("#childrenCount", window.children);
    }
    updateGuestsSummary();
};

function updateGuestElement(selector, value) {
    var $el = $(selector);
    if ($el.length === 0) return;
    if ($el.is('input')) {
        $el.val(value);
    } else {
        $el.text(value);
    }
}

function updateGuestsSummary() {
    var summary = window.adults + ' ' + (window.adults === 1 ? 'взрослый' : 'взрослых');
    if (window.children > 0) {
        summary += ', ' + window.children + ' ' + (window.children === 1 ? 'ребёнок' : 'детей');
    } else {
        summary += ' без детей';
    }
    var $sum = $("#guestsSummary");
    if ($sum.length) $sum.text(summary);
}

// Переменная для API
const API_URL = window.location.origin.replace(':80', '') + ':8000';

$(document).ready(function () {

    // ========== 1. ЗАГРУЗКА ГОРОДОВ ИЗ BACKEND ==========
    function loadCities() {
        $.ajax({
            url: API_URL + '/cities',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.cities && data.cities.length) {
                    updateCityDropdowns(data.cities);
                } else {
                    console.warn('Города не найдены');
                    useFallbackCities();
                }
            },
            error: function () {
                console.error('Ошибка загрузки городов');
                useFallbackCities();
            }
        });
    }

    function updateCityDropdowns(cities) {
        // Обновляем выпадающий список в навигации
        var $navDropdown = $('#cityDropdownList');
        if ($navDropdown.length) {
            $navDropdown.empty();
            $.each(cities, function (i, city) {
                $navDropdown.append('<li><a class="dropdown-item" href="filter.php?city=' + encodeURIComponent(city) + '" data-city="' + city + '">' + city + '</a></li>');
            });
            // Мы не делаем e.preventDefault() для навигационного меню, 
            // чтобы переход на filter.php сработал автоматически.
        }

        // Обновляем список в поисковой форме
        var $cityList = $('#cityList');
        if ($cityList.length) {
            $cityList.empty();
            $.each(cities, function (i, city) {
                $cityList.append(
                    '<li><a href="#" class="dropdown-item city-item d-flex align-items-center" data-name="' + city + '">' +
                    '<i class="bi bi-geo-alt me-2 text-muted"></i><span>' + city + '</span></a></li>'
                );
            });
            $(document).off('click', '.city-item').on('click', '.city-item', function (e) {
                e.preventDefault();
                var name = $(this).data('name');
                $('#citySelector').val(name);
                $('#selectedCity').text(name);
                $('#cityHint').text('Город выбран');
                $(this).addClass('active').siblings().removeClass('active');
            });
        }
    }

    function useFallbackCities() {
        var fallback = ['Москва', 'Санкт-Петербург', 'Казань', 'Сочи', 'Екатеринбург', 'Новосибирск'];
        updateCityDropdowns(fallback);
    }

    loadCities();

    // ========== 2. СЛАЙДЕР ЦЕНЫ ==========
    if ($("#slider-range").length) {
        var startMin = parseInt($("#minPrice").val()) || 0;
        var startMax = parseInt($("#maxPrice").val()) || 20000;
        
        $("#slider-range").slider({
            range: true,
            min: 0,
            max: 50000,
            step: 500,
            values: [startMin, startMax],
            slide: function (event, ui) {
                $("#minPrice").val(ui.values[0]);
                $("#maxPrice").val(ui.values[1]);
            }
        });
        // Устанавливаем значения в инпутах, если они пустые (для подстраховки)
        if (!$("#minPrice").val()) $("#minPrice").val($("#slider-range").slider("values", 0));
        if (!$("#maxPrice").val()) $("#maxPrice").val($("#slider-range").slider("values", 1));

        $("#minPrice").on("input", function () {
            var val = parseInt($(this).val()) || 0;
            val = Math.round(val / 500) * 500;
            var max = $("#slider-range").slider("values", 1);
            if (val < max) {
                $("#slider-range").slider("values", 0, val);
            } else {
                $(this).val(max - 500);
            }
        });

        $("#maxPrice").on("input", function () {
            var val = parseInt($(this).val()) || 50000;
            val = Math.round(val / 500) * 500;
            var min = $("#slider-range").slider("values", 0);
            if (val > min) {
                $("#slider-range").slider("values", 1, val);
            } else {
                $(this).val(min + 500);
            }
        });
    }

    // ========== 3. DATEPICKER ==========
    if ($("#checkinDate").length) {
        $("#checkinDate").datepicker({
            dateFormat: "dd.mm.yy",
            minDate: 0,
            monthNames: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
            dayNamesMin: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
            firstDay: 1
        });
    }
    if ($("#checkoutDate").length) {
        $("#checkoutDate").datepicker({
            dateFormat: "dd.mm.yy",
            minDate: 1,
            monthNames: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
            dayNamesMin: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
            firstDay: 1
        });
    }

    // ========== 4. СЧЁТЧИК ГОСТЕЙ (Инициализация) ==========
    // Синхронизируем из URL если есть
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('adults')) {
        window.adults = parseInt(urlParams.get('adults'));
        updateGuestElement("#adultsCount", window.adults);
    }
    if (urlParams.get('children')) {
        window.children = parseInt(urlParams.get('children'));
        updateGuestElement("#childrenCount", window.children);
    }
    updateGuestsSummary();

    // ========== 5. ИЗБРАННОЕ (Синхронизация с аккаунтом) ==========
    function getCurrentUserId() {
        return (window.phpSessionUser && window.phpSessionUser.id) ? window.phpSessionUser.id : null;
    }

    // Кэш избранного для быстрой отрисовки
    window.cachedFavorites = [];

    function loadFavorites() {
        return new Promise((resolve) => {
            const userId = getCurrentUserId();
            if (!userId) {
                console.log('Favorites Sync: Using localStorage (Guest)');
                try {
                    window.cachedFavorites = JSON.parse(localStorage.getItem('bronic_favorites') || '[]');
                } catch (e) {
                    window.cachedFavorites = [];
                }
                resolve(window.cachedFavorites);
                return;
            }

            console.log('Favorites Sync: Fetching from API for user', userId);
            $.ajax({
                url: API_URL + '/favorites?user_id=' + userId,
                method: 'GET',
                success: function(res) {
                    window.cachedFavorites = res.results || [];
                    console.log('Favorites Sync: Loaded from API:', window.cachedFavorites);
                    
                    // МИГРАЦИЯ: если в localStorage есть "гостевые" избранные, переносим их в аккаунт
                    let local = [];
                    try { local = JSON.parse(localStorage.getItem('bronic_favorites') || '[]'); } catch(e){}
                    if (local.length > 0) {
                        console.log('Favorites Sync: Migrating local favorites to account...');
                        const migratePromises = local.map(item => {
                            return $.ajax({
                                url: API_URL + '/favorites/toggle',
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({ user_id: userId, resource_id: item.id })
                            });
                        });
                        Promise.all(migratePromises).then(() => {
                            localStorage.removeItem('bronic_favorites');
                            // Перезагружаем после миграции
                            $.get(API_URL + '/favorites?user_id=' + userId, function(r) {
                                window.cachedFavorites = r.results || [];
                                resolve(window.cachedFavorites);
                            });
                        });
                    } else {
                        resolve(window.cachedFavorites);
                    }
                },
                error: function() {
                    window.cachedFavorites = [];
                    resolve([]);
                }
            });
        });
    }

    function toggleFavorite(item, $btn) {
        const userId = getCurrentUserId();
        if (!userId) {
            // Если не залогинен - по старинке в localStorage
            let favs = [];
            try { favs = JSON.parse(localStorage.getItem('bronic_favorites') || '[]'); } catch(e){}
            const idx = favs.findIndex(f => String(f.id) === String(item.id));
            
            if (idx >= 0) {
                favs.splice(idx, 1);
                $btn.find('i').removeClass('bi-heart-fill text-danger').addClass('bi-heart');
            } else {
                favs.push(item);
                $btn.find('i').removeClass('bi-heart').addClass('bi-heart-fill text-danger');
            }
            localStorage.setItem('bronic_favorites', JSON.stringify(favs));
            window.cachedFavorites = favs;
            return;
        }

        // Если залогинен - через API
        $.ajax({
            url: API_URL + '/favorites/toggle',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ user_id: userId, resource_id: item.id }),
            success: function(res) {
                if (res.status === 'added') {
                    $btn.find('i').removeClass('bi-heart').addClass('bi-heart-fill text-danger');
                    $btn.attr('title', 'В избранном');
                } else {
                    $btn.find('i').removeClass('bi-heart-fill text-danger').addClass('bi-heart');
                    $btn.attr('title', 'Добавить в избранное');
                }
                // Обновляем кэш и иконки
                loadFavorites().then(() => {
                    window.syncFavoriteIcons();
                });
            }
        });
    }

    window.syncFavoriteIcons = function() {
        if (!window.cachedFavorites) return;
        const favIds = window.cachedFavorites.map(f => parseInt(f.id));
        $('.btn-favorite').each(function () {
            const $btn = $(this);
            const id = parseInt($btn.data('item-id') || $btn.closest('[data-id]').data('id'));
            if (id && favIds.includes(id)) {
                $btn.find('i').removeClass('bi-heart').addClass('bi-heart-fill text-danger');
                $btn.attr('title', 'В избранном');
            } else {
                $btn.find('i').removeClass('bi-heart-fill text-danger').addClass('bi-heart');
                $btn.attr('title', 'Добавить в избранное');
            }
        });
    }

    // ========== 5. КАРТОЧКИ — навигация по клику (игнорируем кнопки) ==========
    $(document).on('click', '.property-card', function (e) {
        // Не переходим если кликнули на кнопку или ссылку
        if ($(e.target).closest('button, a').length) return;
        var id = $(this).data('prop-id') || $(this).closest('[data-id]').data('id');
        if (id) {
            var url = 'property.php?id=' + id;
            var urlParams = new URLSearchParams(window.location.search);
            
            var checkin = $('#checkinDate').val() || urlParams.get('checkin');
            var checkout = $('#checkoutDate').val() || urlParams.get('checkout');
            var adults = window.adults || urlParams.get('adults') || 2;
            var children = window.children || urlParams.get('children') || 0;

            if (checkin) url += '&checkin=' + encodeURIComponent(checkin);
            if (checkout) url += '&checkout=' + encodeURIComponent(checkout);
            url += '&adults=' + adults + '&children=' + children;

            window.location = url;
        }
    });

    // Инициализация избранного
    loadFavorites().then(() => {
        window.syncFavoriteIcons();
    });

    // ========== 6. ИЗБРАННОЕ (Обработка клика) ==========
    $(document).on('click', '.btn-favorite', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var itemData = $btn.data('item');
        var item = null;

        if (itemData) {
            try {
                item = typeof itemData === 'string' ? JSON.parse(itemData) : itemData;
            } catch (ex) { }
        }

        if (!item) {
            var $card = $btn.closest('[data-id]');
            item = {
                id: parseInt($card.data('id')),
                name: $card.find('.card-title').text(),
                price_per_night: $card.data('price'),
                type: $card.data('type'),
                image_url: $card.find('img').attr('src')
            };
        }

        if (!item || !item.id) return;
        
        toggleFavorite(item, $btn);
    });

    // ========== 6. ПОКАЗАТЬ ТЕЛЕФОН ==========
    $(document).on('click', '.btn-show-phone', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var isVisible = $btn.data('phone-visible') === true;
        
        if (isVisible) {
            $btn.html('<i class="bi bi-telephone me-2"></i>Показать телефон')
                .removeClass('btn-success')
                .addClass($btn.hasClass('btn-outline-secondary-mode') ? 'btn-outline-secondary' : 'btn-outline-primary');
            $btn.data('phone-visible', false);
        } else {
            // Запоминаем какой был изначальный стиль, если это вторичная кнопка
            if ($btn.hasClass('btn-outline-secondary')) {
                $btn.addClass('btn-outline-secondary-mode');
            }
            $btn.html('<i class="bi bi-telephone me-2"></i>+7 (495) 123-45-67')
                .removeClass('btn-outline-primary btn-outline-secondary')
                .addClass('btn-success');
            $btn.data('phone-visible', true);
        }
    });

    // ========== 7. БРОНИРОВАНИЕ ==========
    $(document).on('click', '.btn-book', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var id = $btn.data('id') || '';
        var name = encodeURIComponent($btn.data('name') || '');
        var price = $btn.data('price') || '';
        var location = encodeURIComponent($btn.data('location') || '');
        
        var urlParams = new URLSearchParams(window.location.search);
        var checkin = $('#checkinDate').val() || urlParams.get('checkin') || '';
        var checkout = $('#checkoutDate').val() || urlParams.get('checkout') || '';
        var adults = window.adults || urlParams.get('adults') || 2;
        var children = window.children || urlParams.get('children') || 0;

        var url = 'booking.php?id=' + id + '&name=' + name + '&price=' + price + '&location=' + location;
        if (checkin) url += '&checkin=' + encodeURIComponent(checkin);
        if (checkout) url += '&checkout=' + encodeURIComponent(checkout);
        url += '&adults=' + adults + '&children=' + children;

        window.location.href = url;
    });

    // ========== 8. ФИЛЬТРАЦИЯ ==========
    function filterProperties() {
        var minPrice = parseInt($("#minPrice").val()) || 0;
        var maxPrice = parseInt($("#maxPrice").val()) || 50000;
        
        var selectedTypes = [];
        $('input[name="property[]"]:checked').each(function () {
            selectedTypes.push($(this).val());
        });

        var selectedAmenities = [];
        $('input[name="amenities[]"]:checked').each(function () {
            selectedAmenities.push($(this).val());
        });

        var minRating = $('input[name="rating"]:checked').val();
        if (minRating === 'any') minRating = 0;
        minRating = parseFloat(minRating) || 0;

        var visibleCount = 0;
        $('.property-item').each(function () {
            var $item = $(this);
            var price = parseInt($item.data('price')) || 0;
            var type = $item.data('type');
            var rating = parseFloat($item.data('rating')) || 0;
            
            var amenities = [];
            try {
                var amData = $item.attr('data-amenities');
                amenities = typeof amData === 'string' ? JSON.parse(amData) : (amData || []);
            } catch(e) {
                amenities = [];
            }
            
            var priceMatch = (price >= minPrice && price <= maxPrice);
            var typeMatch = (selectedTypes.length === 0 || selectedTypes.indexOf(type) !== -1);
            var ratingMatch = (rating >= minRating);
            
            var amenitiesMatch = true;
            if (selectedAmenities.length > 0) {
                // Все выбранные удобства должны присутствовать в объекте
                for (var i = 0; i < selectedAmenities.length; i++) {
                    if (amenities.indexOf(selectedAmenities[i]) === -1) {
                        amenitiesMatch = false;
                        break;
                    }
                }
            }
            
            if (priceMatch && typeMatch && ratingMatch && amenitiesMatch) {
                $item.show();
                visibleCount++;
            } else {
                $item.hide();
            }
        });
        $('#noResults').toggleClass('d-none', visibleCount > 0);
    }

    $('#filterForm').on('submit', function (e) {
        // Если мы НЕ на странице filter.php (например, на главной), 
        // делаем фильтрацию на лету.
        if (window.location.pathname.indexOf('filter.php') === -1) {
            e.preventDefault();
            filterProperties();
        }
        // Иначе (на filter.php) даем форме отправиться на сервер (стандартный GET)
    });

    $('#resetFilters').on('click', function () {
        $('#filterForm')[0].reset();
        $("#slider-range").slider("values", [0, 20000]);
        $("#minPrice").val(0);
        $("#maxPrice").val(20000);
        if (window.location.pathname.indexOf('filter.php') === -1) {
            filterProperties();
        } else {
            window.location.href = 'filter.php';
        }
    });

    // ========== 9. ПОИСК НА ГЛАВНОЙ ==========
    $('#searchForm').on('submit', function (e) {
        e.preventDefault();
        var city = $('#citySelector').val() || '';
        var checkin = $('#checkinDate').val() || '';
        var checkout = $('#checkoutDate').val() || '';
        var adults = window.adults || 2;
        var children = window.children || 0;

        var url = 'filter.php?city=' + encodeURIComponent(city) +
                  '&checkin=' + encodeURIComponent(checkin) +
                  '&checkout=' + encodeURIComponent(checkout) +
                  '&adults=' + adults +
                  '&children=' + children;
        
        window.location.href = url;
    });

    // Фильтр городов в выпадающем списке
    $('#citySearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#cityList li").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // ========== 10. ЗАГРУЗКА ОБЪЕКТОВ НА ГЛАВНОЙ ==========
    function loadAllProperties() {
        $('#loadingSpinner').show();
        $('#noResults').addClass('d-none');

        $.ajax({
            url: API_URL + '/search',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({}),
            success: function (response) {
                $('#loadingSpinner').hide();
                if (!response.results || response.results.length === 0) {
                    $('#noResults').removeClass('d-none');
                    return;
                }
                window.renderPropertyCards(response.results, '#searchResults');
            },
            error: function (xhr) {
                $('#loadingSpinner').hide();
                $('#searchResults').html('<div class="col-12"><div class="alert alert-danger">Ошибка загрузки данных</div></div>');
            }
        });
    }

    window.renderPropertyCards = function(items, containerId) {
        var $container = $(containerId);
        $container.empty();

        var typeNames = {
            'apartment': 'Квартира',
            'dacha': 'Дача',
            'room': 'Комната',
            'cottedzh': 'Коттедж'
        };

        var favs = window.cachedFavorites || [];
        var favIds = favs.map(function (f) { return parseInt(f.id); });

        $.each(items, function (index, item) {
            var typeName = typeNames[item.type] || 'Недвижимость';
            var priceVal = parseFloat(item.price_per_night) || 0;
            var priceFormatted = priceVal.toLocaleString('ru-RU');
            var name = escapeHtml(item.name || 'Без названия');
            var address = escapeHtml(item.address || item.location || 'Адрес не указан');
            var description = escapeHtml(item.description || 'Описание отсутствует');
            var imgUrl = item.image_url || '../img/property/metro-plus.png';
            
            var isFav = favIds.includes(item.id);
            var heartClass = isFav ? 'bi-heart-fill text-danger' : 'bi-heart';
            var reviewCount = item.review_count || 0;
            var avgRating = parseFloat(item.avg_rating || 0);
            var ratingHtml = avgRating > 0 
                ? `<div class="fw-bold"><i class="bi bi-star-fill text-warning me-1"></i>${avgRating.toFixed(1)}</div>` 
                : `<div class="text-muted small">Нет оценок</div>`;
            
            // Подготовка аменитис для data-атрибута
            var amenitiesStr = '';
            if (Array.isArray(item.amenities)) {
                amenitiesStr = JSON.stringify(item.amenities);
            } else if (typeof item.amenities === 'string') {
                amenitiesStr = item.amenities; // Предполагаем JSON-строку из БД
            } else {
                amenitiesStr = '[]';
            }

            var itemJson = JSON.stringify(item).replace(/"/g, '&quot;');

            var cardHtml = `
                <div class="col-12 mb-4 property-item" 
                     data-id="${item.id}" 
                     data-type="${item.type}" 
                     data-price="${item.price_per_night}"
                     data-rating="${avgRating}"
                     data-amenities='${amenitiesStr}'>
                    <div class="property-card card border-0 shadow-sm" style="cursor:pointer;">
                        <div class="row g-0">
                            <div class="col-md-4 position-relative">
                                <img src="${imgUrl}" class="img-fluid rounded-start h-100 w-100 object-fit-cover" alt="${name}" style="min-height: 200px;" onerror="this.src='../img/property/metro-plus.png'">
                                <button class="btn btn-favorite position-absolute top-0 end-0 m-3 border-0" title="${isFav ? 'В избранном' : 'Добавить в избранное'}" data-item='${itemJson}' data-item-id="${item.id}">
                                    <i class="bi ${heartClass}"></i>
                                </button>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="card-title mb-1 fw-bold">${name}</h5>
                                            <p class="card-text text-muted mb-0"><i class="bi bi-geo-alt-fill text-danger me-1"></i>${address}</p>
                                        </div>
                                        <div class="text-end">
                                            ${ratingHtml}
                                            <small class="text-muted">${reviewCount > 0 ? reviewCount + " отзывов" : "Новинка"}</small>
                                        </div>
                                    </div>
                                    <hr>
                                    <p class="card-text text-muted mb-3">${description}</p>
                                    <div class="mb-3 d-flex gap-2 flex-wrap">
                                        <span class="badge bg-light text-dark"><i class="bi bi-tag me-1"></i>${typeName}</span>
                                        <span class="badge bg-light text-dark"><i class="bi bi-people me-1"></i>${item.guests || 2} гост.</span>
                                        <span class="badge bg-light text-dark"><i class="bi bi-door-open me-1"></i>${item.bedrooms || 1} сп.</span>
                                        <span class="badge bg-light text-dark"><i class="bi bi-aspect-ratio me-1"></i>${item.area || 45} м²</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="fw-bold fs-4 text-danger">${priceFormatted} ₽ <span class="text-muted fs-6 fw-normal">/ сутки</span></div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-outline-primary btn-show-phone" data-phone-visible="false"><i class="bi bi-telephone me-2"></i>Показать телефон</button>
                                            <button class="btn btn-danger btn-book" data-id="${item.id}" data-name="${name}" data-price="${item.price_per_night}" data-location="${address}">Забронировать <i class="bi bi-arrow-right ms-1"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            $container.append(cardHtml);
        });
        
        window.syncFavoriteIcons();
    }

    function escapeHtml(str) {
        if (!str) return '';
        return $('<div>').text(str).html();
    }

    // Загружаем объекты если мы на главной (не на странице фильтра)
    if ($('#searchResults').length && window.location.pathname.indexOf('filter.php') === -1) {
        loadAllProperties();
    }
});
