# Инструкция по запуску проекта BRONIC.RU

## Структура проекта

```
курсач/
├── backend/              # Python FastAPI бэкенд
│   ├── main.py          # Основной файл приложения
│   ├── models/          # Pydantic модели
│   ├── pages/           # HTML шаблоны (не используются)
│   ├── .env             # Переменные окружения (DB_URL)
│   ├── create_tables.sql # SQL скрипт для БД
│   └── start_server.bat # Скрипт запуска бэкенда
├── assets/              # CSS стили
├── img/                 # Изображения
├── inc/                 # PHP инклуды (_nav, _footer и т.д.)
├── index.php            # Главная страница
├── login.php            # Страница входа
├── register.php         # Страница регистрации
├── filter.php           # Результаты поиска
├── booking.php          # Бронирование
├── login_process.php    # Обработчик входа
├── register_process.php # Обработчик регистрации
└── search_api.php       # API поиска
```

---

## Шаг 1: Установка PostgreSQL

1. Скачай установщик: https://www.postgresql.org/download/windows/
2. Установи PostgreSQL 15 или 16
3. **Запомни пароль** для пользователя `postgres`

### Создание базы данных

Открой **SQL Shell (psql)** из меню Пуск и выполни:

```sql
CREATE DATABASE creating_software;
\c creating_software;
GRANT ALL ON SCHEMA public TO postgres;
```

Затем выполни содержимое файла `backend/create_tables.sql`

---

## Шаг 2: Настройка .env файла

Открой файл `backend/.env` и укажи свой пароль:

```env
DB_URL=postgresql://postgres:ТВОЙ_ПАРОЛЬ@localhost:5432/creating_software
```

---

## Шаг 3: Запуск бэкенда

### Вариант А: Через .bat файл (просто)

1. Открой `backend/start_server.bat`
2. Сервер запустится на порту **8000**

### Вариант Б: Вручную через консоль

```bash
cd C:\xampp\htdocs\курсач\backend
py -m uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

### Проверка работы

Открой в браузере: http://localhost:8000/v0/version

Если видишь `{"version": ...}` — бэкенд работает!

---

## Шаг 4: Запуск фронтенда

1. Убедись, что **XAMPP запущен** (Apache и MySQL)
2. Открой браузер: http://localhost/курсач/

Или если используешь виртуальный хост:
- http://bronic.local/

---

## Шаг 5: Тестирование

### Регистрация
1. Перейди на http://localhost/курсач/register.php
2. Заполни форму (имя, фамилия, email, пароль)
3. Нажми "Зарегистрироваться"

### Вход
1. Перейди на http://localhost/курсач/login.php
2. Введи email и пароль
3. Нажми "Войти"

### Поиск недвижимости
1. На главной странице выбери город
2. Укажи даты заезда/выезда
3. Нажми "Смотреть цены"
4. Должны отобразиться карточки из базы данных

---

## Возможные проблемы

### Ошибка: "Бэкенд недоступен"

**Решение:**
1. Проверь, запущен ли бэкенд (см. Шаг 3)
2. Проверь, что порт 8000 не занят

### Ошибка: "Connection refused" к PostgreSQL

**Решение:**
1. Проверь, запущена ли служба PostgreSQL
2. Проверь правильность пароля в `.env`
3. Убедись, что база данных `creating_software` создана

### Ошибка CORS

**Решение:**
Добавь в `backend/main.py` после создания app:

```python
from fastapi.middleware.cors import CORSMiddleware

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

---

## API Endpoints бэкенда

| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/` | Главная страница |
| GET | `/register` | Страница регистрации |
| POST | `/register` | Регистрация (JSON: name, surname, email, password) |
| GET | `/login` | Страница входа |
| POST | `/login` | Вход (JSON: email, password) |
| POST | `/search` | Поиск (JSON: location, type, date_from, date_to) |
| GET | `/v0/version` | Версия API |

---

## Контакты для тестов

Из `create_tables.sql`:

| Email | Пароль | Роль |
|-------|--------|------|
| test@example.com | password123 | user |
| admin@example.com | password123 | admin |

> ⚠️ Пароли хешируются через bcrypt, поэтому для реальных пользователей нужно регистрироваться через форму.

---

## Что дальше?

1. **Добавить cities API** — создать endpoint для списка городов
2. **Добавить профиль пользователя** — страница с данными и бронированиями
3. **Добавить создание недвижимости** — админка для добавления ресурсов
4. **Улучшить бронирование** — реальное сохранение бронирований в БД
