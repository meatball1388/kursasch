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
    <title>Фильтр - BRONIC.RU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <?php include 'inc/_nav.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-3"><?php include 'inc/_aside.php'; ?></div>
            <div class="col-lg-9">
                <h2 class="mb-4">Результаты поиска</h2>
                <?php
                $propertyTypes = isset($_GET['property']) ? $_GET['property'] : [];
                $minPrice = isset($_GET['minPrice']) ? intval($_GET['minPrice']) : 0;
                $maxPrice = isset($_GET['maxPrice']) ? intval($_GET['maxPrice']) : 50000;
                $city = isset($_GET['city']) ? htmlspecialchars($_GET['city']) : '';
                $checkin = isset($_GET['checkin']) ? htmlspecialchars($_GET['checkin']) : '';
                $checkout = isset($_GET['checkout']) ? htmlspecialchars($_GET['checkout']) : '';

                $filters = [];
                if (!empty($city))
                    $filters[] = "Город: " . $city;
                if (!empty($checkin))
                    $filters[] = "Заезд: " . $checkin;
                if (!empty($checkout))
                    $filters[] = "Отъезд: " . $checkout;
                if (!empty($propertyTypes)) {
                    $typeNames = ['appartment' => 'Квартира', 'dacha' => 'Дача', 'room' => 'Комната', 'cottedzh' => 'Коттедж'];
                    $types = array_map(function ($t) use ($typeNames) {
                        return $typeNames[$t] ?? $t;
                    }, $propertyTypes);
                    $filters[] = "Тип: " . implode(', ', $types);
                }
                if ($minPrice > 0 || $maxPrice < 50000)
                    $filters[] = "Цена: " . $minPrice . " - " . $maxPrice . " ₽";
                if (!empty($filters))
                    echo '<div class="alert alert-info mb-4"><i class="bi bi-funnel me-2"></i>' . implode(' | ', $filters) . '</div>';
                ?>
                <div id="searchResults" class="row">
                    <div class="col-12 text-center py-5" id="loadingSpinner">
                        <div class="spinner-border text-danger" style="width:3rem;height:3rem;"></div>
                        <p class="mt-3 text-muted">Ищем варианты...</p>
                    </div>
                </div>
                <div id="noResults" class="alert alert-info d-none"><i class="bi bi-info-circle me-2"></i>По вашему
                    запросу ничего не найдено. Попробуйте изменить фильтры.</div>
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
            function formatDateForApi(dateStr) {
                if (!dateStr) return '';
                var parts = dateStr.split('.');
                return parts.length === 3 ? parts[2] + '-' + parts[1] + '-' + parts[0] : dateStr;
            }
            var searchParams = {
                location: '<?php echo addslashes($city); ?>',
                date_from: formatDateForApi('<?php echo addslashes($checkin); ?>'),
                date_to: formatDateForApi('<?php echo addslashes($checkout); ?>')
            };
            // Получаем выбранные типы из URL (переданные через GET)
            var selectedTypes = <?php echo json_encode($propertyTypes); ?>;
            var minPriceFilter = <?php echo intval($minPrice); ?>;
            var maxPriceFilter = <?php echo intval($maxPrice); ?>;

            $.ajax({
                url: 'http://localhost:8000/search',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(searchParams),
                success: function (response) {
                    $('#loadingSpinner').hide();
                    if (!response.results || response.results.length === 0) {
                        $('#noResults').removeClass('d-none');
                        return;
                    }
                    // Фильтруем полученные результаты по типу и цене
                    var filtered = response.results.filter(function (item) {
                        var typeMatch = (selectedTypes.length === 0 || selectedTypes.indexOf(
                            item.type) !== -1);
                        var priceMatch = (item.base_price >= minPriceFilter && item
                            .base_price <= maxPriceFilter);
                        return typeMatch && priceMatch;
                    });
                    if (filtered.length === 0) {
                        $('#noResults').removeClass('d-none');
                        return;
                    }
                    $('#searchResults').empty();
                    $.each(filtered, function (index, item) {
                        var typeNames = {
                            'appartment': 'Квартира',
                            'dacha': 'Дача',
                            'room': 'Комната',
                            'cottedzh': 'Коттедж'
                        };
                        var typeName = typeNames[item.type] || 'Недвижимость';
                        var priceFormatted = Number(item.base_price).toLocaleString('ru-RU');
                        var name = $('<div>').text(item.name || 'Без названия').html();
                        var address = $('<div>').text(item.address || item.location ||
                            'Адрес не указан').html();
                        var description = $('<div>').text(item.description ||
                            'Описание отсутствует').html();
                        var imgUrl = item.image_url || '../img/property/metro-plus.png';
                        var reviewCount = item.review_count || 0;
                        var ratingVal = item.avg_rating > 0 ? parseFloat(item.avg_rating).toFixed(1) : '4.8';
                        var cardHtml = `
                            <div class="col-12 mb-4 property-item" data-type="${item.type}" data-price="${item.base_price}">
                                <div class="property-card card border-0 shadow-sm" style="cursor:pointer;" data-prop-id="${item.id}">
                                    <div class="row g-0">
                                        <div class="col-md-4 position-relative">
                                            <img src="${imgUrl}" class="img-fluid rounded-start" alt="${name.replace(/"/g, '&quot;')}" onerror="this.src='../img/property/metro-plus.png'">
                                            <button class="btn btn-favorite position-absolute top-0 end-0 m-3 border-0"><i class="bi bi-heart"></i></button>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body p-4">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div><h5 class="card-title mb-1 fw-bold">${name}</h5><p class="card-text text-muted mb-0"><i class="bi bi-geo-alt-fill text-danger me-1"></i>${address}</p></div>
                                                    <div class="text-end"><div class="fw-bold"><i class="bi bi-star-fill text-warning me-1"></i>4.5</div><small class="text-muted">(\$\{reviewCount\} отзывов)</small></div>
                                                </div>
                                                <hr>
                                                <p class="card-text text-muted mb-3">${description}</p>
                                                <div class="mb-3"><span class="badge bg-light text-dark me-1"><i class="bi bi-tag me-1"></i>${typeName}</span></div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="fw-bold fs-4 text-danger">${priceFormatted} ₽ <span class="text-muted fs-6 fw-normal">/ сутки</span></div>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-outline-primary btn-show-phone" data-phone-visible="false"><i class="bi bi-telephone me-1"></i>Показать телефон</button>
                                                        <button class="btn btn-danger btn-book" data-name="${name}" data-price="${item.base_price}" data-location="${address}">Забронировать <i class="bi bi-arrow-right ms-1"></i></button>
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
                error: function (xhr, status, error) {
                    $('#loadingSpinner').hide();
                    $('#searchResults').html(
                        '<div class="col-12"><div class="alert alert-danger">Ошибка загрузки данных: ' +
                        (error || 'Неизвестная ошибка') + '</div></div>');
                }
            });
        });
    </script>
</body>

</html>



