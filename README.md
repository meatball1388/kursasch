# 🏠 BRONIC.RU — Сервис бронирования жилья

Современное веб-приложение для поиска и бронирования недвижимости по всей России с использованием рекомендательной системы на базе ИИ.

---

## 🚀 Быстрый старт (через Docker)

Это самый простой и рекомендуемый способ запуска. Вам не нужно устанавливать PHP, Python или PostgreSQL отдельно.

### 1. Требования
*   Установленный [Docker Desktop](https://www.docker.com/products/docker-desktop/)
*   Docker Desktop должен быть запущен

### 2. Запуск
Открой терминал в корне проекта и выполни:
```powershell
docker-compose up --build -d
```

### 3. Доступ к приложению
*   **Сайт:** [http://localhost](http://localhost)
*   **API Документация (Swagger):** [http://localhost:8000/docs](http://localhost:8000/docs)

---

## 🛠 Технологический стек

*   **Frontend:** PHP 8.2, Apache, Bootstrap 5, jQuery.
*   **Backend:** Python 3.11, FastAPI, Asyncpg.
*   **Database:** PostgreSQL 16.
*   **AI/ML:** Scikit-learn (Random Forest) для персональных рекомендаций.

---

## 📂 Структура проекта

```text
C:\xampp\htdocs\kursasch\
├── backend/               # Python API (FastAPI)
│   ├── models/           # Pydantic модели данных
│   ├── ai_recommender.py # Логика машинного обучения
│   ├── ai_router.py      # Эндпоинты для ИИ
│   ├── main.py           # Основной файл приложения
│   └── init_db.sql       # Схема и данные БД
├── front/                 # PHP Фронтенд
│   ├── assets/           # CSS стили
│   ├── img/              # Изображения (объекты, фоны)
│   ├── inc/              # PHP компоненты (шапка, подвал)
│   ├── index.php         # Главная страница
│   └── property.php      # Страница объекта
└── docker-compose.yml     # Конфигурация контейнеров
```

---

## 🔐 Учётные данные (для тестов)

| Роль | Email | Пароль |
|---|---|---|
| **Администратор** | `admin@example.com` | `admin` |
| **Тестовый юзер** | `test@example.com` | `password123` |

**Параметры БД (внутри Docker):**
*   **User:** `postgres`
*   **Password:** `1234`
*   **Database:** `creating_software`

---

## 💡 Полезные команды

*   `docker-compose logs -f` — просмотр логов в реальном времени.
*   `docker-compose down` — остановка проекта.
*   `docker-compose down -v` — полная очистка (удаление базы данных).
*   `docker-compose restart backend` — перезапуск только бэкенда.

---

## 🤖 Особенности ИИ-модели
Система рекомендаций использует алгоритм **Random Forest**. 
Для корректной работы при первом запуске рекомендуется нажать кнопку **"Обучить модель"** в блоке рекомендаций на главной странице. Модель анализирует город, тип жилья, бюджет и количество гостей для подбора лучших вариантов.
