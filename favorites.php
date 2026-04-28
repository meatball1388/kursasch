<?php
session_start();
?>
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
    <title>Избранное - BRONIC.RU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include 'inc/_nav.php'; ?>

    <section class="bg-light py-5 border-bottom">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="mb-3"><i class="bi bi-heart me-2" style="color: #fe496a;"></i>Избранное</h1>
                    <p class="text-muted">Сохранённые объекты для вашего будущего путешествия</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="text-muted mb-0"><span class="fw-bold" id="favoritesCount">0</span> объектов в избранном</p>
            <div class="btn-group">
                <button class="btn btn-outline-secondary btn-sm active" id="gridViewBtn"><i class="bi bi-grid"></i></button>
                <button class="btn btn-outline-secondary btn-sm" id="listViewBtn"><i class="bi bi-list"></i></button>
            </div>
        </div>

        <div class="row g-4" id="favoritesGrid"></div>

        <div class="text-center py-5 d-none" id="emptyFavorites">
            <i class="bi bi-heart fs-1 text-muted d-block mb-3"></i>
            <h5 class="fw-bold">В избранном пока пусто</h5>
            <p class="text-muted">Нажимайте на сердечко на карточках объектов, чтобы добавить их сюда</p>
            <a href="index.php" class="btn btn-danger mt-2"><i class="bi bi-search me-2"></i>Найти жильё</a>
        </div>
    </div>

    <?php include 'inc/_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="main.js"></script>
    <script>
    $(function() {
        let isGridView = true;

        function getFavorites() {
            try { return JSON.parse(localStorage.getItem('bronic_favorites') || '[]'); }
            catch(e) { return []; }
        }

        function buildCard(item, gridMode) {
            const img = item.image_url || './img/property/room_example.png';
            const price = Number(item.base_price).toLocaleString('ru-RU');
            const typeNames = { 'appartment': 'Квартира', 'dacha': 'Дача', 'room': 'Комната', 'cottedzh': 'Коттедж' };
            const typeName = typeNames[item.type] || 'Недвижимость';

            if (!gridMode) {
                return `
                <div class="col-12 fav-item" data-id="${item.id}">
                    <div class="card border-0 shadow-sm">
                        <div class="row g-0">
                            <div class="col-md-3 position-relative">
                                <img src="${img}" class="img-fluid rounded-start h-100 w-100 object-fit-cover" style="min-height:160px;" alt="${item.name}">
                                <button class="btn btn-light rounded-circle position-absolute top-0 end-0 m-2 text-danger btn-remove-fav" data-id="${item.id}" title="Удалить из избранного">
                                    <i class="bi bi-heart-fill"></i>
                                </button>
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="fw-bold mb-1">${item.name}</h6>
                                            <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i>${item.address || item.location}</p>
                                            <span class="badge bg-light text-dark"><i class="bi bi-tag me-1"></i>${typeName}</span>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold fs-5">${price} ₽</div>
                                            <div class="text-muted small">/ сутки</div>
                                            <a href="booking.php?id=${item.id}&name=${encodeURIComponent(item.name)}&price=${item.base_price}&location=${encodeURIComponent(item.location)}" class="btn btn-danger btn-sm mt-2">Забронировать</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            }

            return `
            <div class="col-md-6 col-lg-4 fav-item" data-id="${item.id}">
                <div class="card border-0 shadow-sm h-100">
                    <div class="position-relative">
                        <img src="${img}" class="card-img-top" style="height:200px;object-fit:cover;" alt="${item.name}">
                        <button class="btn btn-light rounded-circle position-absolute top-0 end-0 m-2 text-danger btn-remove-fav" data-id="${item.id}" title="Удалить из избранного">
                            <i class="bi bi-heart-fill"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-1">${item.name}</h6>
                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i>${item.address || item.location}</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span class="fw-bold fs-5">${price} ₽</span>
                                <span class="text-muted small"> / сутки</span>
                            </div>
                            <a href="booking.php?id=${item.id}&name=${encodeURIComponent(item.name)}&price=${item.base_price}&location=${encodeURIComponent(item.location)}" class="btn btn-danger btn-sm">Забронировать</a>
                        </div>
                    </div>
                </div>
            </div>`;
        }

        function render() {
            const favs = getFavorites();
            $('#favoritesCount').text(favs.length);
            const grid = $('#favoritesGrid');
            grid.empty();

            if (favs.length === 0) {
                $('#emptyFavorites').removeClass('d-none');
                return;
            }

            $('#emptyFavorites').addClass('d-none');
            favs.forEach(item => grid.append(buildCard(item, isGridView)));
        }

        // Переключение вид
        $('#gridViewBtn').on('click', function() {
            isGridView = true;
            $(this).addClass('active');
            $('#listViewBtn').removeClass('active');
            render();
        });
        $('#listViewBtn').on('click', function() {
            isGridView = false;
            $(this).addClass('active');
            $('#gridViewBtn').removeClass('active');
            render();
        });

        // Удаление из избранного
        $(document).on('click', '.btn-remove-fav', function() {
            const id = parseInt($(this).data('id'));
            let favs = getFavorites().filter(f => f.id !== id);
            localStorage.setItem('bronic_favorites', JSON.stringify(favs));
            render();
        });

        render();
    });
    </script>
</body>
</html>
