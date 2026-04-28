<nav class="navbar navbar-dark bg-dark border-bottom border-secondary">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php"><span
                class="fw-bold fs-4 text-white me-2">BRONIC.RU</span></a>
        <div class="d-flex align-items-center gap-4">
            <a href="rent.php" class="text-white text-decoration-none d-flex align-items-center gap-2"><i
                    class="bi bi-house-door"></i><span>Сдать жильё</span></a>
            <a href="bookings.php" class="text-white text-decoration-none d-flex align-items-center gap-2"><i
                    class="bi bi-calendar-check"></i><span>Бронирования</span></a>
            <a href="favorites.php" class="text-white text-decoration-none d-flex align-items-center gap-2"><i
                    class="bi bi-heart"></i><span>Избранное</span></a>
            <?php
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['user']) && $_SESSION['user']['logged_in']): 
                $userName = explode('@', $_SESSION['user']['email'])[0];
            ?>
                <div class="dropdown">
                    <button class="btn btn-link text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2 p-0 border-0" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-check-fill"></i><span><?= htmlspecialchars($userName) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                            <li><a class="dropdown-item text-primary" href="admin.php"><i class="bi bi-shield-lock me-2"></i>Панель администратора</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="bookings.php"><i class="bi bi-calendar-check me-2"></i>Мои бронирования</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Выйти</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="login.php" class="text-white text-decoration-none d-flex align-items-center gap-2"><i
                        class="bi bi-person-circle"></i><span>Войти</span></a>
            <?php endif; ?>
            <div class="dropdown">
                <button
                    class="btn btn-link text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2"
                    type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-geo-alt"></i>
                    <span>Город</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" id="cityDropdownList" style="max-height:250px;overflow-y:auto;">
                    <!-- Сюда будет загружен список городов из базы -->
                </ul>
            </div>
            <div class="dropdown">
                <button
                    class="btn btn-link text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2"
                    type="button" data-bs-toggle="dropdown"><img src="https://flagcdn.com/w20/ru.png" alt="RU"
                        width="20"><span>RUB</span></button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><img src="https://flagcdn.com/w20/ru.png" alt="RU" width="20"
                                class="me-2"> RUB</a></li>
                    <li><a class="dropdown-item" href="#"><img src="https://flagcdn.com/w20/us.png" alt="US" width="20"
                                class="me-2"> USD</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-link text-white text-decoration-none p-0 border-0"
                data-bs-toggle="modal" data-bs-target="#supportModal" title="Поддержка"><i
                    class="bi bi-headset fs-5"></i></button>
        </div>
    </div>
</nav>

<!-- Модальное окно поддержки (единственное) -->
<div class="modal fade" id="supportModal" tabindex="-1" aria-labelledby="supportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="supportModalLabel"><i class="bi bi-headset me-2"
                        style="color: #fe496a;"></i>Служба поддержки</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="support-phone mb-3 text-center">
                    <p class="text-muted mb-2 small">Горячая линия (круглосуточно):</p>
                    <a href="tel:+78001234567" class="support-phone-link text-decoration-none fw-bold"
                        style="font-size: 1.25rem; color: #fe496a;"><i class="bi bi-telephone-fill me-2"></i>8 (800)
                        123-45-67</a>
                    <p class="small text-muted mt-1">Звонок по России бесплатный</p>
                </div>
                <hr>
                <h6 class="mb-3">Или оставьте сообщение:</h6>
                <form action="#" method="POST">
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label for="supportName" class="form-label small">Ваше имя</label><input
                                type="text" class="form-control form-control-sm" id="supportName" name="name"
                                placeholder="Иван" required></div>
                        <div class="col-6"><label for="supportPhone" class="form-label small">Телефон</label><input
                                type="tel" class="form-control form-control-sm" id="supportPhone" name="phone"
                                placeholder="+7 (___) ___-__-__" required></div>
                    </div>
                    <div class="mb-2"><label for="supportEmail" class="form-label small">Email</label><input
                            type="email" class="form-control form-control-sm" id="supportEmail" name="email"
                            placeholder="example@mail.ru" required></div>
                    <div class="mb-2"><label for="supportSubject" class="form-label small">Тема обращения</label><select
                            class="form-select form-select-sm" id="supportSubject" name="subject" required>
                            <option value="" selected disabled>Выберите тему</option>
                            <option value="booking">Проблема с бронированием</option>
                            <option value="payment">Вопросы оплаты</option>
                            <option value="cancellation">Отмена бронирования</option>
                            <option value="account">Проблемы с аккаунтом</option>
                            <option value="other">Другое</option>
                        </select></div>
                    <div class="mb-3"><label for="supportMessage" class="form-label small">Опишите
                            проблему</label><textarea class="form-control form-control-sm" id="supportMessage"
                            name="message" rows="3" placeholder="Подробно опишите вашу проблему..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 py-2"
                        style="background-color: #fe496a; border: none;"><i class="bi bi-send me-2"></i>Отправить
                        обращение</button>
                    <p class="small text-muted text-center mt-2 mb-0">Мы ответим вам в течение 24 часов</p>
                </form>
            </div>
        </div>
    </div>
</div>