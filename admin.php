<?php
session_start();
if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in'] || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo "<h1>Доступ запрещен</h1>";
    echo "<p>Для доступа к этой странице необходимо войти как администратор.</p>";
    echo "<a href='index.php'>На главную</a>";
    exit;
}
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
    <title>Панель администратора - BRONIC.RU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <?php include 'inc/_nav.php'; ?>

    <div class="container py-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0 text-primary"><i class="bi bi-shield-lock me-2"></i>Панель администратора</h2>
                    <span class="badge bg-danger p-2 fs-6">ADMIN MODE</span>
                </div>

                <!-- Навигация по вкладкам -->
                <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="true">Пользователи</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab" aria-controls="resources" aria-selected="false">Объекты</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab" aria-controls="bookings" aria-selected="false">Бронирования</button>
                    </li>
                </ul>

                <!-- Содержимое вкладок -->
                <div class="tab-content" id="adminTabsContent">
                    <!-- Вкладка Пользователи -->
                    <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Имя</th>
                                        <th>Email</th>
                                        <th>Роль</th>
                                        <th>Создан</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <!-- Данные будут здесь -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Вкладка Объекты -->
                    <div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Название</th>
                                        <th>Тип</th>
                                        <th>Цена</th>
                                        <th>Город</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="resourcesTableBody">
                                    <!-- Данные будут здесь -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Вкладка Бронирования -->
                    <div class="tab-pane fade" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Клиент</th>
                                        <th>Объект</th>
                                        <th>Заезд</th>
                                        <th>Выезд</th>
                                        <th>Сумма</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="bookingsTableBody">
                                    <!-- Данные будут здесь -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно редактирования -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <!-- Поля формы будут вставлены динамически -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" id="saveEditBtn">Сохранить</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'inc/_footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            let currentTable = 'users';
            let currentId = null;

            function loadTable(table) {
                currentTable = table;
                $.ajax({
                    url: 'http://localhost:8000/admin_api',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ action: 'get_all', table: table }),
                    success: function(response) {
                        if (response.error) {
                            alert(response.error);
                            return;
                        }
                        renderTable(table, response.results);
                    }
                });
            }

            function renderTable(table, data) {
                const tbody = $(`#${table}TableBody`);
                tbody.empty();
                data.forEach(item => {
                    let row = '';
                    if (table === 'users') {
                        row = `
                            <tr>
                                <td>${item.id}</td>
                                <td>${item.name || ''} ${item.surname || ''}</td>
                                <td>${item.email}</td>
                                <td><span class="badge ${item.role === 'admin' ? 'bg-danger' : 'bg-primary'}">${item.role}</span></td>
                                <td>${item.created_at || ''}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${item.id}"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${item.id}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>`;
                    } else if (table === 'resources') {
                        row = `
                            <tr>
                                <td>${item.id}</td>
                                <td>${item.name}</td>
                                <td>${item.type}</td>
                                <td>${item.base_price} ₽</td>
                                <td>${item.location}</td>
                                <td><span class="badge ${item.is_active ? 'bg-success' : 'bg-secondary'}">${item.is_active ? 'Активен' : 'Отключен'}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${item.id}"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${item.id}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>`;
                    } else if (table === 'bookings') {
                        const statusColors = {
                            'CREATED': 'bg-warning text-dark',
                            'CONFIRMED': 'bg-success',
                            'PAID': 'bg-primary',
                            'CANCELLED': 'bg-secondary'
                        };
                        const badgeClass = statusColors[item.status] || 'bg-info';
                        const price = Number(item.price).toLocaleString('ru-RU');
                        const dateFrom = item.start_time ? item.start_time.split('T')[0] : '—';
                        const dateTo   = item.end_time   ? item.end_time.split('T')[0]   : '—';
                        const clientInfo = item.user_name || item.user_email || `ID: ${item.user_id}`;
                        const resourceInfo = item.resource_name || `ID: ${item.resource_id}`;
                        row = `
                            <tr>
                                <td><span class="font-monospace text-muted small">#${item.id}</span></td>
                                <td>
                                    <div class="fw-semibold">${clientInfo}</div>
                                    <div class="text-muted small">${item.user_email || ''}</div>
                                </td>
                                <td><span class="fw-semibold">${resourceInfo}</span></td>
                                <td>${dateFrom}</td>
                                <td>${dateTo}</td>
                                <td class="fw-bold">${price} ₽</td>
                                <td><span class="badge ${badgeClass}">${item.status}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${item.id}"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${item.id}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>`;
                    }
                    tbody.append(row);
                });
            }

            // Загрузка при переключении вкладок
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                const target = $(e.target).data('bs-target').replace('#', '');
                loadTable(target);
            });

            // Удаление
            $(document).on('click', '.delete-btn', function() {
                if (!confirm('Вы уверены, что хотите удалить эту запись?')) return;
                const id = $(this).data('id');
                $.ajax({
                    url: 'http://localhost:8000/admin_api',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ action: 'delete', table: currentTable, id: id }),
                    success: function(response) {
                        if (response.success) loadTable(currentTable);
                        else alert('Ошибка удаления');
                    }
                });
            });

            // Редактирование (открытие модалки)
            $(document).on('click', '.edit-btn', function() {
                currentId = $(this).data('id');
                const row = $(this).closest('tr');
                let html = '';

                if (currentTable === 'users') {
                    const role = row.find('.badge').text();
                    html = `
                        <div class="mb-3">
                            <label class="form-label">Роль</label>
                            <select class="form-select" id="edit-role">
                                <option value="user" ${role === 'user' ? 'selected' : ''}>User</option>
                                <option value="admin" ${role === 'admin' ? 'selected' : ''}>Admin</option>
                            </select>
                        </div>`;
                } else if (currentTable === 'resources') {
                    const price = row.find('td:nth-child(4)').text().replace(' ₽', '');
                    const status = row.find('.badge').text() === 'Активен';
                    html = `
                        <div class="mb-3">
                            <label class="form-label">Базовая цена</label>
                            <input type="number" class="form-control" id="edit-price" value="${price}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Статус</label>
                            <select class="form-select" id="edit-active">
                                <option value="true" ${status ? 'selected' : ''}>Активен</option>
                                <option value="false" ${!status ? 'selected' : ''}>Отключен</option>
                            </select>
                        </div>`;
                } else if (currentTable === 'bookings') {
                    const status = row.find('.badge').text();
                    html = `
                        <div class="mb-3">
                            <label class="form-label">Статус</label>
                            <select class="form-select" id="edit-status">
                                <option value="CREATED" ${status === 'CREATED' ? 'selected' : ''}>CREATED</option>
                                <option value="CONFIRMED" ${status === 'CONFIRMED' ? 'selected' : ''}>CONFIRMED</option>
                                <option value="CANCELLED" ${status === 'CANCELLED' ? 'selected' : ''}>CANCELLED</option>
                                <option value="PAID" ${status === 'PAID' ? 'selected' : ''}>PAID</option>
                            </select>
                        </div>`;
                }

                $('#editModalBody').html(html);
                $('#editModal').modal('show');
            });

            // Сохранение изменений
            $('#saveEditBtn').on('click', function() {
                let fields = {};
                if (currentTable === 'users') {
                    fields.role = $('#edit-role').val();
                } else if (currentTable === 'resources') {
                    fields.base_price = parseFloat($('#edit-price').val());
                    fields.is_active = $('#edit-active').val() === 'true';
                } else if (currentTable === 'bookings') {
                    fields.status = $('#edit-status').val();
                }

                $.ajax({
                    url: 'http://localhost:8000/admin_api',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ 
                        action: 'update', 
                        table: currentTable, 
                        id: currentId,
                        fields: fields
                    }),
                    success: function(response) {
                        if (response.success) {
                            $('#editModal').modal('hide');
                            loadTable(currentTable);
                        } else alert('Ошибка сохранения');
                    }
                });
            });

            // Первая загрузка
            loadTable('users');
        });
    </script>
</body>
</html>
