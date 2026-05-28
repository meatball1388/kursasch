<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои бронирования - BRONIC.RU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="icon" href="../img/bronic.png" type="image/png">
</head>
<body>
    <?php include 'inc/_nav.php'; ?>

    <section class="bg-light py-5 border-bottom">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="mb-3">
                        <i class="bi bi-calendar-check me-2" style="color: #fe496a;"></i>
                        Мои бронирования
                    </h1>
                    <p class="text-muted">Управляйте вашими текущими и прошлыми бронированиями</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-5">
        <?php if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']): ?>
            <div class="alert alert-warning text-center py-5">
                <i class="bi bi-person-lock fs-1 d-block mb-3"></i>
                <h5>Войдите в аккаунт</h5>
                <p class="text-muted">Для просмотра бронирований необходимо авторизоваться</p>
                <a href="login.php" class="btn btn-danger mt-2"><i class="bi bi-box-arrow-in-right me-2"></i>Войти</a>
            </div>
        <?php else: ?>
        <div class="row">
            <div class="col-lg-12">
                <ul class="nav nav-tabs mb-4" id="bookingTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" id="current-tab" data-bs-toggle="tab" data-bs-target="#current" type="button">
                        <i class="bi bi-clock-history me-2"></i>Активные</button></li>
                    <li class="nav-item"><button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button">
                        <i class="bi bi-archive me-2"></i>Завершённые / Отменённые</button></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="current" role="tabpanel">
                        <div id="currentBookings"><div class="text-center py-5"><div class="spinner-border text-danger" role="status"></div></div></div>
                    </div>
                    <div class="tab-pane fade" id="past" role="tabpanel">
                        <div id="pastBookings"><div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Модальное окно деталей -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Детали бронирования</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsModalBody"></div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'inc/_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="main.js"></script>
    <script>
    <?php if (isset($_SESSION['user']) && $_SESSION['user']['logged_in']): ?>
    $(function() {
        function statusBadge(status) {
            const map = {
                'CREATED':   '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Ожидает</span>',
                'CONFIRMED': '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Подтверждено</span>',
                'PAID':      '<span class="badge bg-primary"><i class="bi bi-credit-card me-1"></i>Оплачено</span>',
                'CANCELLED': '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Отменено</span>',
            };
            return map[status] || `<span class="badge bg-light text-dark">${status}</span>`;
        }

        function buildCard(b, active) {
            const name = b.resource_name || `Объект #${b.resource_id}`;
            const address = b.address || b.location || '';
            const img = b.image_url || '../img/property/metro-plus.png';
            const dateFrom = b.start_time ? b.start_time.split('T')[0] : '—';
            const dateTo   = b.end_time   ? b.end_time.split('T')[0]   : '—';
            const price = Number(b.price).toLocaleString('ru-RU');
            const cancelBtn = active
                ? `<button class="btn btn-outline-danger w-100 mb-2 btn-cancel" data-id="${b.id}"><i class="bi bi-x-circle me-1"></i>Отменить</button>`
                : '';
            const commentStr = b.comment ? b.comment.replace(/"/g, '&quot;') : '';
            
            let payBtn = '';
            if (active && b.status === 'CREATED') {
                payBtn = `<button class="btn btn-primary w-100 mb-2 btn-pay-now" data-id="${b.id}" data-amount="${b.price}">
                            <i class="bi bi-credit-card me-1"></i>Оплатить
                          </button>`;
            }

            return `
            <div class="card border-0 shadow-sm mb-3" id="booking-${b.id}">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <img src="${img}" class="img-fluid rounded" style="height:160px;object-fit:cover;width:100%;" alt="${name}">
                        </div>
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-1">${name}</h5>
                            <p class="text-muted mb-2"><i class="bi bi-geo-alt me-1"></i>${address}</p>
                            ${statusBadge(b.status)}
                            <div class="mt-2 text-muted small">
                                <i class="bi bi-calendar3 me-1"></i>${dateFrom} → ${dateTo}
                            </div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <div class="fw-bold fs-5 mb-3">${price} ₽</div>
                            ${payBtn}
                            ${cancelBtn}
                            <button class="btn btn-outline-secondary w-100 btn-details"
                                data-name="${name}" data-addr="${address}"
                                data-from="${dateFrom}" data-to="${dateTo}"
                                data-price="${price}" data-status="${b.status}" data-id="${b.id}" data-comment="${commentStr}">
                                <i class="bi bi-file-text me-1"></i>Детали
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        }

        function loadBookings() {
            const userId = "<?php echo isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : ''; ?>";
            if (!userId) {
                $('#currentBookings').html('<div class="alert alert-warning">Пожалуйста, войдите в аккаунт</div>');
                return;
            }
            
            $.ajax({
                url: 'http://' + (window.location.hostname || 'localhost') + ':8000/my-bookings?user_id=' + userId,
                method: 'GET',
                success: function(res) {
                    const myBookings = res.bookings || [];
                    
                    const active   = myBookings.filter(b => ['CREATED', 'CONFIRMED', 'PAID', 'SUCCESS'].includes(b.status));
                    const inactive = myBookings.filter(b => ['CANCELLED', 'COMPLETED', 'EXPIRED'].includes(b.status));

                    if (active.length === 0) {
                        $('#currentBookings').html('<div class="text-center py-5"><i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i><p class="text-muted">У вас нет активных бронирований</p><a href="index.php" class="btn btn-danger mt-2">Найти жильё</a></div>');
                    } else {
                        $('#currentBookings').html(active.map(b => buildCard(b, true)).join(''));
                    }

                    if (inactive.length === 0) {
                        $('#pastBookings').html('<div class="text-center py-5"><i class="bi bi-archive fs-1 text-muted d-block mb-3"></i><p class="text-muted">Нет завершённых бронирований</p></div>');
                    } else {
                        $('#pastBookings').html(inactive.map(b => buildCard(b, false)).join(''));
                    }
                },
                error: function() {
                    $('#currentBookings').html('<div class="alert alert-danger">Ошибка загрузки данных</div>');
                }
            });
        }

        // Отмена
        $(document).on('click', '.btn-cancel', function() {
            if (!confirm('Вы уверены, что хотите отменить это бронирование?')) return;
            const id = $(this).data('id');
            $.ajax({
                url: 'http://' + (window.location.hostname || 'localhost') + ':8000/admin_api',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ action: 'update', table: 'bookings', id: id, fields: { status: 'CANCELLED' } }),
                success: function(res) {
                    if (res.success) {
                        loadBookings();
                    } else {
                        alert('Ошибка при отмене');
                    }
                }
            });
        });

        // Оплата сейчас
        $(document).on('click', '.btn-pay-now', function() {
            const id = $(this).data('id');
            const amount = $(this).data('amount');
            
            $.ajax({
                url: 'http://' + (window.location.hostname || 'localhost') + ':8000/payments/create',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    booking_id: id,
                    amount: amount
                }),
                success: function(payRes) {
                    if (payRes.confirmation_url) {
                        window.location.href = payRes.confirmation_url;
                    } else if (payRes.error) {
                        alert('Ошибка ЮKassa: ' + payRes.error);
                    } else {
                        alert('Ошибка инициализации платежа');
                    }
                },
                error: function() {
                    alert('Ошибка сервера при создании платежа');
                }
            });
        });

        // Детали
        $(document).on('click', '.btn-details', function() {
            const d = $(this).data();
            const wishesRow = d.comment ? `<tr><td class="text-muted">Пожелания</td><td class="fst-italic">${d.comment}</td></tr>` : '';
            $('#detailsModalBody').html(`
                <table class="table table-borderless">
                    <tr><td class="text-muted">Объект</td><td class="fw-bold">${d.name}</td></tr>
                    <tr><td class="text-muted">Адрес</td><td>${d.addr}</td></tr>
                    <tr><td class="text-muted">Заезд</td><td>${d.from}</td></tr>
                    <tr><td class="text-muted">Выезд</td><td>${d.to}</td></tr>
                    <tr><td class="text-muted">Стоимость</td><td class="fw-bold text-danger">${d.price} ₽</td></tr>
                    <tr><td class="text-muted">Статус</td><td>${d.status}</td></tr>
                    <tr><td class="text-muted">№ брони</td><td class="font-monospace">#${d.id}</td></tr>
                    ${wishesRow}
                </table>
            `);
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        });

        loadBookings();
    });
    <?php endif; ?>
    </script>
</body>
</html>
