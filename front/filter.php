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
    <link rel="icon" href="../img/bronic.png" type="image/png">
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
                    $typeNames = ['apartment' => 'Квартира', 'dacha' => 'Дача', 'room' => 'Комната', 'cottedzh' => 'Коттедж'];
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
                url: 'http://' + (window.location.hostname || 'localhost') + ':8000/search',
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
                    
                    if (typeof window.renderPropertyCards === 'function') {
                        window.renderPropertyCards(filtered, '#searchResults');
                    } else {
                        console.error('renderPropertyCards is not defined');
                    }
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



