# 🐳 Запуск проекта через Docker

## Структура контейнеров

| Контейнер | Образ | Порт | Назначение |
|---|---|---|---|
| `kursach_db` | postgres:16 | 5432 | PostgreSQL |
| `kursach_backend` | python:3.11 | 8000 | FastAPI |
| `kursach_frontend` | php:8.2-apache | 80 | PHP-фронт |

## Быстрый старт

### 1. Убедись, что Docker Desktop запущен

### 2. Перейди в корень проекта
```powershell
cd C:\xampp\htdocs\kursach
```

### 3. Запусти всё одной командой
```powershell
docker-compose up --build -d
```

> `-d` — запуск в фоне (detached mode)  
> `--build` — пересобрать образы (нужно при первом запуске или изменении кода)

### 4. Открой браузер
- **Сайт**: http://localhost
- **API**: http://localhost:8000
- **API-документация**: http://localhost:8000/docs

---

## Учётные данные

| | |
|---|---|
| **Логин администратора** | admin@example.com |
| **Пароль** | admin |
| **БД пользователь** | postgres |
| **БД пароль** | 1234 |
| **БД имя** | creating_software |

---

## Полезные команды

```powershell
# Посмотреть логи всех контейнеров
docker-compose logs -f

# Логи только бэкенда
docker-compose logs -f backend

# Остановить всё (данные БД сохранятся)
docker-compose down

# Остановить И удалить БД (полный сброс)
docker-compose down -v

# Перезапустить только бэкенд
docker-compose restart backend

# Зайти в контейнер БД
docker exec -it kursach_db psql -U postgres -d creating_software
```

---

## Разработка (hot reload)

Бэкенд (`backend/`) и фронт (`front/`) примонтированы как volume — изменения в файлах применяются сразу:
- Python: uvicorn запущен с `--reload`, автоперезагрузка при изменении `.py`
- PHP: Apache сразу видит изменения в `.php`

---

## Возможные проблемы

**Порт 80 занят (XAMPP ещё запущен)**
```powershell
# Останови Apache в XAMPP перед запуском Docker
# Или измени порт в docker-compose.yml:
#   ports: - "8080:80"
```

**База не инициализировалась**
```powershell
# Удали volume и пересоздай
docker-compose down -v
docker-compose up --build -d
```

**Ошибка WSL / Docker не запускается**
```powershell
wsl --update
```
