$(document).ready(function () {

    // ========== 1. ЗАГРУЗКА ГОРОДОВ ИЗ BACKEND ==========
    function loadCities() {
        $.ajax({
            url: 'http://localhost:8000/cities',
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
                $navDropdown.append('<li><a class="dropdown-item" href="#" data-city="' + city + '">' + city + '</a></li>');
            });
            $navDropdown.find('.dropdown-item').on('click', function (e) {
                e.preventDefault();
                var selectedCity = $(this).data('city');
                $('#selectedCity').text(selectedCity);
                $('#citySelector').val(selectedCity);
                $('#cityHint').text('Город выбран');
            });
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
        $("#slider-range").slider({
            range: true,
            min: 0,
            max: 50000,
            step: 500,
            values: [0, 20000],
            slide: function (event, ui) {
                $("#minPrice").val(ui.values[0]);
                $("#maxPrice").val(ui.values[1]);
            }
        });
        $("#minPrice").val($("#slider-range").slider("values", 0));
        $("#maxPrice").val($("#slider-range").slider("values", 1));

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

    // ========== 4. СЧЁТЧИК ГОСТЕЙ ==========
    window.adults = 2;
    window.children = 0;

    window.changeGuests = function (type, delta) {
        if (type === 'adults') {
            window.adults = Math.max(1, Math.min(10, window.adults + delta));
            $("#adultsCount").text(window.adults);
        } else if (type === 'children') {
            window.children = Math.max(0, Math.min(10, window.children + delta));
            $("#childrenCount").text(window.children);
        }
        updateGuestsSummary();
    }

    function updateGuestsSummary() {
        var summary = window.adults + ' ' + (window.adults === 1 ? 'взрослый' : 'взрослых');
        if (window.children > 0) {
            summary += ', ' + window.children + ' ' + (window.children === 1 ? 'ребёнок' : 'детей');
        } else {
            summary += ' без детей';
        }
        $("#guestsSummary").text(summary);
    }

    // ========== 5. ИЗБРАННОЕ (localStorage) ==========
    function getFavorites() {
        try {
            return JSON.parse(localStorage.getItem('bronic_favorites') || '[]');
        } catch (e) {
            return [];
        }
    }

    function saveFavorites(arr) {
        localStorage.setItem('bronic_favorites', JSON.stringify(arr));
    }

    function syncFavoriteIcons() {
        var favIds = getFavorites().map(function (f) { return f.id; });
        $('.btn-favorite').each(function () {
            var $btn = $(this);
            var id = $btn.data('item-id') || $btn.closest('[data-id]').data('id');
            if (favIds.includes(id)) {
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
        if (id) window.location = 'property.php?id=' + id;
    });

    // ========== 6. ИЗБРАННОЕ ==========
    $(document).on('click', '.btn-favorite', function (e) {
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
                base_price: $card.data('price'),
                type: $card.data('type'),
                image_url: $card.find('img').attr('src')
            };
        }

        if (!item || !item.id) return;

        var favs = getFavorites();
        var existingIndex = favs.findIndex(function (f) { return String(f.id) === String(item.id); });

        if (existingIndex >= 0) {
            favs.splice(existingIndex, 1);
            $btn.find('i').removeClass('bi-heart-fill text-danger').addClass('bi-heart');
            $btn.attr('title', 'Добавить в избранное');
        } else {
            favs.push(item);
            $btn.find('i').removeClass('bi-heart').addClass('bi-heart-fill text-danger');
            $btn.attr('title', 'В избранном');
        }
        saveFavorites(favs);
    });

    // ========== 6. ПОКАЗАТЬ ТЕЛЕФОН ==========
    $(document).on('click', '.btn-show-phone', function () {
        var $btn = $(this);
        if ($btn.data('phone-visible') === true) {
            $btn.html('<i class="bi bi-telephone me-1"></i>Показать телефон').removeClass('btn-success').addClass('btn-outline-primary');
            $btn.data('phone-visible', false);
        } else {
            $btn.html('<i class="bi bi-telephone me-1"></i>+7 (495) 123-45-67').removeClass('btn-outline-primary').addClass('btn-success');
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
        window.location.href = 'booking.php?id=' + id + '&name=' + name + '&price=' + price + '&location=' + location;
    });

    // ========== 8. ФИЛЬТРАЦИЯ НА СТРАНИЦЕ filter.php ==========
    function filterProperties() {
        var minPrice = 0, maxPrice = 50000;
        if ($("#slider-range").length) {
            minPrice = $("#slider-range").slider("values", 0);
            maxPrice = $("#slider-range").slider("values", 1);
        }
        var selectedTypes = [];
        $('input[name="property[]"]:checked').each(function () {
            selectedTypes.push($(this).val());
        });
        var visibleCount = 0;
        $('.property-item').each(function () {
            var $item = $(this);
            var price = parseInt($item.data('price')) || 0;
            var type = $item.data('type');
            var priceMatch = (price >= minPrice && price <= maxPrice);
            var typeMatch = (selectedTypes.length === 0 || selectedTypes.indexOf(type) !== -1);
            if (priceMatch && typeMatch) {
                $item.show();
                visibleCount++;
            } else {
                $item.hide();
            }
        });
        $('#noResults').toggleClass('d-none', visibleCount > 0);
    }

    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        filterProperties();
    });

    // ========== 9. ПОИСК НА ГЛАВНОЙ ==========
    $('#searchForm').on('submit', function () {
        $('<input>').attr({ type: 'hidden', name: 'adults', value: window.adults }).appendTo(this);
        $('<input>').attr({ type: 'hidden', name: 'children', value: window.children }).appendTo(this);
    });

    // ========== 10. ЗАГРУЗКА ОБЪЕКТОВ НА ГЛАВНОЙ ==========
    function loadAllProperties() {
        $('#loadingSpinner').show();
        $('#noResults').addClass('d-none');

        $.ajax({
            url: 'http://localhost:8000/search',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({}),
            success: function (response) {
                $('#loadingSpinner').hide();
                if (!response.results || response.results.length === 0) {
                    $('#noResults').removeClass('d-none');
                    return;
                }
                renderPropertyCards(response.results, '#searchResults');
            },
            error: function (xhr) {
                $('#loadingSpinner').hide();
                $('#searchResults').html('<div class="col-12"><div class="alert alert-danger">Ошибка загрузки данных</div></div>');
            }
        });
    }

    function renderPropertyCards(items, containerId) {
        var $container = $(containerId);
        $container.empty();

        var typeNames = {
            'appartment': 'Квартира',
            'dacha': 'Дача',
            'room': 'Комната',
            'cottedzh': 'Коттедж'
        };

        var favs = getFavorites();
        var favIds = favs.map(function (f) { return f.id; });

        $.each(items, function (index, item) {
            var typeName = typeNames[item.type] || 'Недвижимость';
            var priceFormatted = Number(item.base_price).toLocaleString('ru-RU');
            var name = escapeHtml(item.name || 'Без названия');
            var address = escapeHtml(item.address || item.location || 'Адрес не указан');
            var description = escapeHtml(item.description || 'Описание отсутствует');
            var imgUrl = item.image_url || '/img/property/metro-plus.png';
            console.log('Loading image for ID ' + item.id + ': ' + imgUrl);
            var isFav = favIds.includes(item.id);
            var heartClass = isFav ? 'bi-heart-fill text-danger' : 'bi-heart';
            var reviewCount = item.review_count || 0;
            var ratingDisplay = item.avg_rating > 0 ? parseFloat(item.avg_rating).toFixed(1) : '4.5';
            var itemJson = JSON.stringify(item).replace(/"/g, '&quot;');

            var cardHtml = `
                <div class="col-12 mb-4 property-item" data-id="${item.id}" data-type="${item.type}" data-price="${item.base_price}">
                    <div class="property-card card border-0 shadow-sm" style="cursor:pointer;" onclick="window.location='property.php?id=${item.id}'">
                        <div class="row g-0">
                            <div class="col-md-4 position-relative">
                                <img src="${imgUrl}" class="img-fluid rounded-start h-100 w-100 object-fit-cover" alt="${name}" style="min-height: 200px;">
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
                                            <div class="fw-bold"><i class="bi bi-star-fill text-warning me-1"></i>${ratingDisplay}</div>
                                            <small class="text-muted">${reviewCount > 0 ? reviewCount + " отзывов" : "Новинка"}</small>
                                        </div>
                                    </div>
                                    <hr>
                                    <p class="card-text text-muted mb-3">${description}</p>
                                    <div class="mb-3">
                                        <span class="badge bg-light text-dark me-1"><i class="bi bi-tag me-1"></i>${typeName}</span>
                                    </div>
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
                </div>`;
            $container.append(cardHtml);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return $('<div>').text(str).html();
    }

    // Загружаем объекты если мы на главной
    if ($('#searchResults').length && !window.location.search.includes('city')) {
        loadAllProperties();
    }
});


