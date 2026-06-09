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
                <div class="modal-footer border-0 justify-content-between">
                    <button type="button" class="btn btn-outline-primary" id="btnDownloadDoc" style="display:none;"><i class="bi bi-filetype-pdf me-2"></i>Документ (детали)</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения отмены -->
    <div class="modal fade" id="confirmCancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-exclamation-circle text-danger" style="font-size: 3.5rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Отменить бронирование?</h5>
                    <p class="text-muted small mb-4">Это действие нельзя будет отменить. Вы уверены?</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger py-2 fw-bold" id="confirmCancelBtn" style="border-radius: 12px;">Да, отменить</button>
                        <button type="button" class="btn btn-light py-2 text-muted fw-medium" data-bs-dismiss="modal" style="border-radius: 12px;">Назад</button>
                    </div>
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
        let bookingIdToCancel = null;
        const cancelModal = new bootstrap.Modal(document.getElementById('confirmCancelModal'));

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
            console.log('Building card for booking:', b);
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
            const passportStr = b.passport ? b.passport.replace(/"/g, '&quot;') : '';
            
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
                                data-price="${price}" data-status="${b.status}" data-id="${b.id}" 
                                data-comment="${commentStr}" data-passport="${passportStr}"
                                data-adults="${b.adults || 0}" data-children="${b.children || 0}">
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

        // Отмена (открытие модалки)
        $(document).on('click', '.btn-cancel', function() {
            bookingIdToCancel = $(this).data('id');
            cancelModal.show();
        });

        // Подтверждение отмены
        $('#confirmCancelBtn').on('click', function() {
            if (!bookingIdToCancel) return;
            
            const btn = $(this);
            const originalText = btn.text();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Отмена...');

            $.ajax({
                url: 'http://' + (window.location.hostname || 'localhost') + ':8000/admin_api',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ action: 'update', table: 'bookings', id: bookingIdToCancel, fields: { status: 'CANCELLED' } }),
                success: function(res) {
                    btn.prop('disabled', false).text(originalText);
                    cancelModal.hide();
                    if (res.success) {
                        loadBookings();
                        if (window.showToast) window.showToast('Бронирование отменено', 'success');
                    } else {
                        if (window.showToast) window.showToast('Ошибка при отмене: ' + (res.error || 'Неизвестная ошибка'));
                    }
                },
                error: function() {
                    btn.prop('disabled', false).text(originalText);
                    cancelModal.hide();
                    if (window.showToast) window.showToast('Ошибка сервера при отмене');
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
                        window.showToast('Ошибка ЮKassa: ' + payRes.error);
                    } else {
                        window.showToast('Ошибка инициализации платежа');
                    }
                },
                error: function() {
                    window.showToast('Ошибка сервера при создании платежа');
                }
            });
        });

        // Детали
        $(document).on('click', '.btn-details', function() {
            const $btn = $(this);
            const d = {
                id: $btn.attr('data-id'),
                name: $btn.attr('data-name'),
                addr: $btn.attr('data-addr'),
                from: $btn.attr('data-from'),
                to: $btn.attr('data-to'),
                price: $btn.attr('data-price'),
                status: $btn.attr('data-status'),
                comment: $btn.attr('data-comment'),
                passport: $btn.attr('data-passport'),
                adults: $btn.attr('data-adults') || 0,
                children: $btn.attr('data-children') || 0
            };
            
            // Показываем кнопку скачивания только для успешных/оплаченных/подтвержденных броней
            if (['CONFIRMED', 'PAID', 'SUCCESS'].includes(d.status)) {
                $('#btnDownloadDoc').show().data('booking', d);
            } else {
                $('#btnDownloadDoc').hide();
            }

            $('#detailsModalBody').html(`
                <div class="mb-4">
                    <h6 class="fw-bold small text-muted text-uppercase mb-3">Объект и даты</h6>
                    <table class="table table-borderless align-middle mb-0">
                        <tr><td class="text-muted small" style="width: 35%;">Название</td><td class="fw-bold">${d.name}</td></tr>
                        <tr><td class="text-muted small">Адрес</td><td class="small">${d.addr}</td></tr>
                        <tr><td class="text-muted small">Заезд</td><td class="small">${d.from}</td></tr>
                        <tr><td class="text-muted small">Выезд</td><td class="small">${d.to}</td></tr>
                    </table>
                </div>

                <hr class="my-3 opacity-10">

                <div class="mb-4">
                    <h6 class="fw-bold small text-muted text-uppercase mb-3">Детали заказа</h6>
                    <table class="table table-borderless align-middle mb-0">
                        <tr><td class="text-muted small" style="width: 35%;">Стоимость</td><td class="fw-bold text-danger fs-5">${d.price} ₽</td></tr>
                        <tr><td class="text-muted small">Статус</td><td>${statusBadge(d.status)}</td></tr>
                        <tr><td class="text-muted small">№ брони</td><td class="font-monospace small">#${d.id}</td></tr>
                    </table>
                </div>

                <hr class="my-3 opacity-10">

                <div class="mb-4">
                    <h6 class="fw-bold small text-muted text-uppercase mb-3">Информация о гостях</h6>
                    <div class="d-flex gap-4 mb-3">
                        <div><span class="text-muted small">Взрослых:</span> <span class="fw-bold">${d.adults}</span></div>
                        <div><span class="text-muted small">Детей:</span> <span class="fw-bold">${d.children}</span></div>
                    </div>
                    ${d.passport ? `<div><span class="text-muted small d-block">Паспорт:</span> <span class="small font-monospace">${d.passport}</span></div>` : ''}
                </div>

                <div class="p-3 bg-light rounded-3">
                    <h6 class="fw-bold small text-muted text-uppercase mb-2">Ваши пожелания</h6>
                    <p class="mb-0 fst-italic text-dark small">${d.comment || 'Вы не оставили особых пожеланий'}</p>
                </div>
            `);
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        });

        // Скачивание/печать документа
        $('#btnDownloadDoc').on('click', function() {
            const d = $(this).data('booking');
            if (!d) return;

            const printWindow = window.open('', '_blank', 'width=800,height=900');
            
            const months = ["Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря"];
            const dateObj = new Date();
            const todayStr = dateObj.getDate() + ' ' + months[dateObj.getMonth()] + ' ' + dateObj.getFullYear() + ' г.';
            
            // Расчет количества суток для вывода (примерный, на основе цены)
            let nights = 1;
            if (d.from && d.to) {
                const partsFrom = d.from.split('.');
                const partsTo = d.to.split('.');
                if (partsFrom.length === 3 && partsTo.length === 3) {
                    const df = new Date(partsFrom[2], partsFrom[1]-1, partsFrom[0]);
                    const dt = new Date(partsTo[2], partsTo[1]-1, partsTo[0]);
                    const diffTime = Math.abs(dt - df);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    if (diffDays > 0) nights = diffDays;
                }
            }
            
            const priceNum = Number(d.price.replace(/\s/g, ''));
            const pricePerNight = nights > 0 ? (priceNum / nights).toLocaleString('ru-RU') : d.price;

            const html = `
            <!DOCTYPE html>
            <html lang="ru">
            <head>
                <meta charset="UTF-8">
                <title>Подтверждение бронирования № 0559${d.id}</title>
                <style>
                    body { 
                        font-family: 'Times New Roman', Times, serif; 
                        color: #000; 
                        line-height: 1.3; 
                        padding: 40px; 
                        max-width: 800px; 
                        margin: 0 auto; 
                        font-size: 14px;
                    }
                    .company-info {
                        font-weight: bold;
                        font-style: italic;
                        margin-bottom: 20px;
                        font-size: 13px;
                    }
                    .company-info p { margin: 2px 0; }
                    .header-row {
                        display: flex;
                        justify-content: space-between;
                        font-weight: bold;
                        margin-bottom: 30px;
                    }
                    .title {
                        text-align: center;
                        font-size: 16px;
                        font-weight: bold;
                        margin: 20px 0;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 30px;
                        border: 2px solid #000;
                    }
                    td {
                        border: 1px solid #000;
                        padding: 5px 8px;
                        vertical-align: middle;
                    }
                    .col-label {
                        font-weight: bold;
                        width: 35%;
                    }
                    .totals-row td {
                        font-weight: bold;
                    }
                    .totals-value {
                        text-align: center;
                    }
                    .payment-info {
                        font-weight: bold;
                        margin-bottom: 30px;
                    }
                    .rules {
                        font-style: italic;
                        font-size: 13px;
                        margin-bottom: 40px;
                        text-align: justify;
                    }
                    .rules p { margin: 5px 0; }
                    .footer-signature {
                        font-weight: bold;
                    }
                    .footer-signature p { margin: 2px 0; }
                    @media print {
                        body { padding: 0; }
                    }
                </style>
            </head>
            <body>
                <div class="company-info">
                    <p>ООО "BRONIC Отель"</p>
                    <p>ОГРН: 1093443001442 ИНН/КПП 3443090954/344301001</p>
                    <p>Сч. № 40702810000000002101 в ОАО "НОКССБАНК" г. Москва</p>
                    <p>к/сч. 30101810000000000831 БИК 041806831</p>
                    <p>г.Москва, ул. Тверская, 1</p>
                    <p>Тел: (495) 720-02-32, 795-70-53, (812) 309-34-24</p>
                    <p>site@bronic.ru</p>
                </div>

                <div class="header-row">
                    <div>г.Москва</div>
                    <div>${todayStr}</div>
                </div>

                <div class="title">
                    Подтверждение бронирования № 0559${d.id}
                </div>

                <table>
                    <tr>
                        <td class="col-label">Место размещения</td>
                        <td>${d.name}</td>
                    </tr>
                    <tr>
                        <td class="col-label">Адрес</td>
                        <td>${d.addr}</td>
                    </tr>
                    <tr>
                        <td class="col-label">Категория номера</td>
                        <td>Стандартный</td>
                    </tr>
                    <tr>
                        <td class="col-label">Период проживания</td>
                        <td>${d.from} - ${d.to} г.</td>
                    </tr>
                    <tr>
                        <td class="col-label">Заезд / Выезд</td>
                        <td>14.00 / 12.00</td>
                    </tr>
                    <tr>
                        <td class="col-label">Ф.И.О. Гостей</td>
                        <td>${'Взрослых: ' + d.adults + ', Детей: ' + d.children}</td>
                    </tr>
                    <tr>
                        <td class="col-label">В стоимость входит</td>
                        <td>проживание</td>
                    </tr>
                    <tr>
                        <td class="col-label">Доп. Услуги (не<br>входящие в стоимость)</td>
                        <td>${d.comment || ''}</td>
                    </tr>
                    <tr>
                        <td class="col-label">Расчет стоимости</td>
                        <td>(${pricePerNight} руб. * ${nights} суток ) = ${d.price} руб.</td>
                    </tr>
                    <tr class="totals-row">
                        <td class="col-label">Итого:</td>
                        <td class="totals-value">${d.price}.00 руб.</td>
                    </tr>
                </table>

                <div class="payment-info">
                    Оплата: По безналичному расчету (Оплачено)
                </div>

                <div class="rules">
                    <p><b>Расчетный час в гостинице время заезда / выезда - 14.00/12.00</b></p>
                    <p>Аннуляция бронирования осуществляется не позднее чем за 2 рабочих дня до заявленной даты заезда и принимается только в письменном виде на фирменном бланке бронирующей организации.</p>
                    <p>При несвоевременном уведомлении ООО "BRONIC Отель" об аннуляции или опоздании гостей будет удержана стоимость простоя номера (номеров) за каждый день просрочки.</p>
                </div>

                <div class="footer-signature">
                    <p>С уважением менеджер</p>
                    <p>ООО "BRONIC Отель"</p>
                </div>
            </body>
            </html>
            `;
            
            printWindow.document.open();
            printWindow.document.write(html);
            printWindow.document.close();
        });

        loadBookings();
    });
    <?php endif; ?>
    </script>
</body>
</html>
