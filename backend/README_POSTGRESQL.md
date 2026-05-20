# Инструкция по установке PostgreSQL

## Шаг 1: Скачивание и установка

1. Перейди на сайт: https://www.postgresql.org/download/windows/
2. Нажми **"Download the installer"** от EnterpriseDB
3. Скачай версию **PostgreSQL 15** или **16** (последнюю стабильную)
4. Запусти установщик

## Шаг 2: Настройка при установке

Во время установки:

1. **Port**: оставь `5432` (по умолчанию)
2. **Locale**: выбери `Russian, Russia (RU)` или `English, United States (US)`
3. **Password для суперпользователя postgres**: запомни его! (например: `postgres123`)

## Шаг 3: Создание базы данных

После установки открой **pgAdmin 4** (установится вместе с PostgreSQL) или **SQL Shell (psql)**:

### Через SQL Shell (psql):

1. Открой меню Пуск → PostgreSQL → **SQL Shell (psql)**
2. Нажимай Enter для принятия значений по умолчанию
3. Введи пароль для пользователя `postgres`

Выполни команды:

```sql
-- Создать базу данных
CREATE DATABASE creating_software;

-- Создать пользователя (если нужно)
CREATE USER app_user WITH PASSWORD 'app_password123';

-- Дать права
GRANT ALL PRIVILEGES ON DATABASE creating_software TO app_user;

-- Подключиться к базе
\c creating_software;

-- Дать права на схемы
GRANT ALL ON SCHEMA public TO app_user;
```

## Шаг 4: Создание таблиц

Выполни SQL-скрипт для создания таблиц (файл `create_tables.sql` в этой папке).

## Шаг 5: Настройка .env файла

Создай файл `.env` в папке `backend` с таким содержимым:

```env
DB_URL=postgresql://postgres:postgres123@localhost:5432/creating_software
```

Замени `postgres123` на твой пароль.

## Шаг 6: Проверка подключения

Запусти тестовое подключение:

```bash
cd C:\xampp\htdocs\курсач\backend
py -c "import asyncpg; import asyncio; asyncio.run(asyncpg.connect('postgresql://postgres:postgres123@localhost:5432/creating_software'))"
```

Если ошибок нет — всё работает!

---

## Дальнейшие шаги

После настройки PostgreSQL:

1. Создай файл `.env` с параметрами подключения
2. Запусти сервер: `uvicorn main:app --reload`
3. Открой браузер: http://localhost:8000
