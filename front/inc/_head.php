<!-- Дополнительные стили и скрипты, если нужны -->
<style>
.container,
.container-fluid,
.container-lg,
.container-md,
.container-sm,
.container-xl,
.container-xxl {
    --bs-gutter-x: 0;
    --bs-gutter-y: 0;
    width: 100%;
    padding-right: calc(var(--bs-gutter-x) * .5);
    padding-left: calc(var(--bs-gutter-x) * .5);
    margin-right: auto;
    margin-left: auto;
}

/* Исправление сдвига экрана при открытии модального окна */
body.modal-open {
    overflow: hidden !important;
    padding-right: 0 !important;
}

body.modal-open .navbar {
    padding-right: 0 !important;
}

/* Ограничение высоты модального окна */
.modal-dialog-scrollable {
    max-height: calc(100vh - 2rem);
}

.modal-dialog-scrollable .modal-content {
    max-height: calc(100vh - 2rem);
}

/* Стили для dropdown городов */
#cityList .dropdown-item {
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    transition: background-color 0.2s;
}

#cityList .dropdown-item:hover {
    background-color: #f8f9fa;
}

#cityList .dropdown-item.active {
    background-color: #fe496a;
    color: white;
}

#cityList .dropdown-item.active i {
    color: white;
}

#citySearch:focus {
    border-color: #fe496a;
    box-shadow: 0 0 0 0.2rem rgba(254, 73, 106, 0.25);
}
</style>
