from fastapi import FastAPI, Request, Response, HTTPException, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
import dotenv
import os
import asyncpg
from contextlib import asynccontextmanager
from datetime import datetime, date
import bcrypt
from ai_router import ai_router
import uuid
import json
import shutil
from yookassa import Configuration, Payment as YooPayment

try:
    from cryptography.fernet import Fernet
    HAS_CRYPTO = True
except ImportError:
    HAS_CRYPTO = False
    print("ВНИМАНИЕ: Библиотека cryptography не установлена. Шифрование паспортов будет недоступно.")

'''
API бэкенд для PHP-фронта BRONIC.RU
БД: PostgreSQL, таблицы: users, resources, bookings, payments, messages, services
Фронт: PHP (XAMPP, порт 80) → бэк (FastAPI, порт 8000)
'''

# Загружаем переменные окружения из .env (используем абсолютный путь для надежности)
env_path = os.path.join(os.path.dirname(__file__), '.env')
if os.path.exists(env_path):
    dotenv.load_dotenv(env_path)
else:
    dotenv.load_dotenv()

DB_URL = os.getenv("DB_URL")
if not DB_URL:
    print("ВНИМАНИЕ: Переменная DB_URL не найдена в окружении!")

# Настройка шифрования паспортов
cipher_suite = None
if HAS_CRYPTO:
    PASSPORT_KEY = os.getenv("PASSPORT_ENCRYPTION_KEY")
    if not PASSPORT_KEY:
        # Генерируем временный ключ, если его нет в .env (не рекомендуется для продакшена)
        print("ВНИМАНИЕ: PASSPORT_ENCRYPTION_KEY не найден. Генерация временного ключа.")
        PASSPORT_KEY = Fernet.generate_key().decode()
    try:
        cipher_suite = Fernet(PASSPORT_KEY.encode())
    except Exception as e:
        print(f"ОШИБКА инициализации шифрования: {e}")

def encrypt_data(data: str) -> str:
    if not data or not cipher_suite: return data or ""
    try:
        return cipher_suite.encrypt(data.encode()).decode()
    except Exception:
        return data

def decrypt_data(token: str) -> str:
    if not token or not cipher_suite: return token or ""
    try:
        return cipher_suite.decrypt(token.encode()).decode()
    except Exception:
        # Если это не зашифрованная строка (например, старые данные), возвращаем как есть
        return token

# Настройка ЮKassa
shop_id = (os.getenv("YOOKASSA_SHOP_ID") or "").strip()
secret_key = (os.getenv("YOOKASSA_SECRET_KEY") or "").strip()

if shop_id and secret_key:
    print("DEBUG STARTUP: YooKassa credentials loaded")
else:
    print("ВНИМАНИЕ: Данные ЮKassa (ShopID или Key) не найдены!")

Configuration.account_id = shop_id
Configuration.secret_key = secret_key


@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup — создаём пул соединений
    if not DB_URL:
        raise RuntimeError("DB_URL is not set. Check your .env file in backend folder.")
        
    app.state.pool = await asyncpg.create_pool(
        dsn=DB_URL,
        min_size=5,
        max_size=20
    )
    print("Database pool created")
    
    # Автоматическая миграция: проверка наличия колонок
    async with app.state.pool.acquire() as con:
        # 1. Проверка external_id в payments
        check_pay = await con.fetchval("""
            SELECT count(*) FROM information_schema.columns 
            WHERE table_name='payments' AND column_name='external_id';
        """)
        if check_pay == 0:
            print("Миграция: Добавление колонки external_id в таблицу payments...")
            await con.execute("ALTER TABLE payments ADD COLUMN external_id VARCHAR(100);")

        # 1.5 Переименование base_price в price_per_night
        has_base_price = await con.fetchval("""
            SELECT count(*) FROM information_schema.columns 
            WHERE table_name='resources' AND column_name='base_price';
        """)
        if has_base_price > 0:
            print("Миграция: Переименование base_price в price_per_night...")
            await con.execute("ALTER TABLE resources RENAME COLUMN base_price TO price_per_night;")

        # 2. Проверка колонок в resources (area, guests, bedrooms)
        resource_cols = {
            "area": "INTEGER DEFAULT 0",
            "guests": "INTEGER DEFAULT 0",
            "bedrooms": "INTEGER DEFAULT 0"
        }
        for col, col_def in resource_cols.items():
            check_res = await con.fetchval(f"""
                SELECT count(*) FROM information_schema.columns 
                WHERE table_name='resources' AND column_name='{col}';
            """)
            if check_res == 0:
                print(f"Миграция: Добавление колонки {col} в таблицу resources...")
                await con.execute(f"ALTER TABLE resources ADD COLUMN {col} {col_def};")

        # 3. Проверка таблицы reviews
        check_reviews = await con.fetchval("""
            SELECT count(*) FROM information_schema.tables WHERE table_name='reviews';
        """)
        if check_reviews == 0:
            print("Миграция: Создание таблицы reviews...")
            await con.execute("""
                CREATE TABLE reviews (
                    id SERIAL PRIMARY KEY,
                    resource_id INTEGER REFERENCES resources(id) ON DELETE CASCADE,
                    author_name VARCHAR(100),
                    rating INTEGER DEFAULT 5,
                    comment TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL
                );
            """)
        else:
            # Проверяем наличие колонки user_id если таблица уже есть
            check_user_id = await con.fetchval("""
                SELECT count(*) FROM information_schema.columns 
                WHERE table_name='reviews' AND column_name='user_id';
            """)
            if check_user_id == 0:
                print("Миграция: Добавление колонки user_id в таблицу reviews...")
                await con.execute("ALTER TABLE reviews ADD COLUMN user_id INTEGER REFERENCES users(id) ON DELETE SET NULL;")

        # 4. Проверка колонки comment в bookings
        check_booking_comment = await con.fetchval("""
            SELECT count(*) FROM information_schema.columns 
            WHERE table_name='bookings' AND column_name='comment';
        """)
        if check_booking_comment == 0:
            print("Миграция: Добавление колонки comment в таблицу bookings...")
            await con.execute("ALTER TABLE bookings ADD COLUMN comment TEXT;")

        # 5. Проверка новых таблиц (amenities, guest_profiles, audit_logs, cities, resource_types)
        tables_to_check = {
            "cities": """CREATE TABLE cities (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) UNIQUE NOT NULL
            )""",
            "resource_types": """CREATE TABLE resource_types (
                id SERIAL PRIMARY KEY,
                name VARCHAR(50) UNIQUE NOT NULL,
                display_name VARCHAR(100)
            )""",
            "amenities": """CREATE TABLE amenities (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) UNIQUE NOT NULL,
                icon VARCHAR(100)
            )""",
            "resource_amenities": """CREATE TABLE resource_amenities (
                resource_id INTEGER REFERENCES resources(id) ON DELETE CASCADE,
                amenity_id INTEGER REFERENCES amenities(id) ON DELETE CASCADE,
                PRIMARY KEY (resource_id, amenity_id)
            )""",
            "guest_profiles": """CREATE TABLE guest_profiles (
                id SERIAL PRIMARY KEY,
                booking_id INTEGER REFERENCES bookings(id) ON DELETE CASCADE UNIQUE,
                name VARCHAR(255),
                email VARCHAR(255),
                phone VARCHAR(50),
                passport TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )""",
            "audit_logs": """CREATE TABLE audit_logs (
                id SERIAL PRIMARY KEY,
                action VARCHAR(255) NOT NULL,
                user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
                resource_id INTEGER REFERENCES resources(id) ON DELETE SET NULL,
                details TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"""
        }
        
        for table, create_sql in tables_to_check.items():
            exists = await con.fetchval(f"SELECT count(*) FROM information_schema.tables WHERE table_name='{table}'")
            if exists == 0:
                print(f"Миграция: Создание таблицы {table}...")
                await con.execute(create_sql)
            
        # 6. Проверка колонок city_id и type_id в resources
        res_fk_cols = {
            "city_id": "INTEGER REFERENCES cities(id)",
            "type_id": "INTEGER REFERENCES resource_types(id)"
        }
        for col, col_def in res_fk_cols.items():
            check_fk = await con.fetchval(f"""
                SELECT count(*) FROM information_schema.columns 
                WHERE table_name='resources' AND column_name='{col}';
            """)
            if check_fk == 0:
                print(f"Миграция: Добавление колонки {col} в таблицу resources...")
                await con.execute(f"ALTER TABLE resources ADD COLUMN {col} {col_def};")

        # 6.1 Миграция данных из location/type в city_id/type_id
        # Проверяем, есть ли пустые city_id при наличии location
        need_city_mig = await con.fetchval("SELECT COUNT(*) FROM resources WHERE city_id IS NULL AND location IS NOT NULL")
        if need_city_mig > 0:
            print("Миграция: Заполнение city_id из location...")
            await con.execute("INSERT INTO cities (name) SELECT DISTINCT location FROM resources WHERE location IS NOT NULL ON CONFLICT DO NOTHING")
            await con.execute("UPDATE resources r SET city_id = c.id FROM cities c WHERE r.location = c.name AND r.city_id IS NULL")

        # Предзаполняем типы красивыми именами
        await con.execute("""
            INSERT INTO resource_types (name, display_name) VALUES 
            ('apartment', 'Квартира'),
            ('dacha', 'Дача'),
            ('room', 'Комната'),
            ('cottedzh', 'Коттедж')
            ON CONFLICT (name) DO NOTHING
        """)
        
        need_type_mig = await con.fetchval("SELECT COUNT(*) FROM resources WHERE type_id IS NULL AND type IS NOT NULL")
        if need_type_mig > 0:
            print("Миграция: Заполнение type_id из type...")
            await con.execute("INSERT INTO resource_types (name, display_name) SELECT DISTINCT type, type FROM resources WHERE type IS NOT NULL ON CONFLICT (name) DO NOTHING")
            await con.execute("UPDATE resources r SET type_id = rt.id FROM resource_types rt WHERE r.type = rt.name AND r.type_id IS NULL")

        # 7. Проверка колонок в users (phone, passport)
        user_cols = {
            "phone": "VARCHAR(50)",
            "passport": "TEXT"
        }
        for col, col_def in user_cols.items():
            check_user = await con.fetchval(f"""
                SELECT count(*) FROM information_schema.columns 
                WHERE table_name='users' AND column_name='{col}';
            """)
            if check_user == 0:
                print(f"Миграция: Добавление колонки {col} в таблицу users...")
                await con.execute(f"ALTER TABLE users ADD COLUMN {col} {col_def};")

        # 8. Проверка таблицы favorites
        check_favs = await con.fetchval("""
            SELECT count(*) FROM information_schema.tables WHERE table_name='favorites';
        """)
        if check_favs == 0:
            print("Миграция: Создание таблицы favorites...")
            await con.execute("""
                CREATE TABLE favorites (
                    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
                    resource_id INTEGER REFERENCES resources(id) ON DELETE CASCADE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (user_id, resource_id)
                );
            """)
            
        print("База данных актуальна.")

    yield
    # Shutdown — закрываем пул
    await app.state.pool.close()
    print("Database pool closed")


app = FastAPI(lifespan=lifespan)

# CORS — разрешаем запросы с фронтенда
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=False,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.middleware("http")
async def log_requests(request: Request, call_next):
    origin = request.headers.get("origin")
    print(f"DEBUG: Request {request.method} {request.url} from origin: {origin}")
    response = await call_next(request)
    return response

# Подключаем AI-роутер
app.include_router(ai_router, prefix="/ai", tags=["AI"])


# ==============================================================
# ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
# ==============================================================

def map_image_url(image_url: str, resource_id: int) -> str:
    """Маппинг внешних URL на локальные файлы."""
    if not image_url:
        return "../img/property/metro-plus.png"
    
    # Если это один из наших начальных объектов и у него внешний URL (или заглушка), 
    # маппим его на локальный файл. Для новых объектов (ID > 8) оставляем как есть.
    if "http" in image_url and resource_id <= 8:
        mapping = {
            1: "metro-plus.png",
            2: "lesnau-skazka.webp",
            3: "komnata-arbat.jpg",
            4: "kotedzh-luxery.webp",
            5: "studia.jpg",
            6: "dacha-u-ozera.jpg",
            8: "metro-plus.png"
        }
        fname = mapping.get(resource_id)
        if fname:
            return f"../img/property/{fname}"
    
    return image_url


def parse_date(s: str) -> datetime:
    """Парсим дату из форматов DD.MM.YYYY и YYYY-MM-DD."""
    for fmt in ("%Y-%m-%d", "%d.%m.%Y", "%Y-%m-%dT%H:%M:%S"):
        try:
            return datetime.strptime(s, fmt)
        except ValueError:
            pass
    raise ValueError(f"Не удалось распознать дату: {s}")


# ==============================================================
# ВЕРСИЯ API
# ==============================================================

@app.get("/v0/version")
async def api_version():
    return {"version": "2.0.1-FIXED", "api": "bronik.ru"}


# ==============================================================
# ГОРОДА (GET /cities)
# Фронт: main.js → loadCities()
# ==============================================================

@app.get("/cities")
async def get_cities(request: Request):
    print("DEBUG: Received request for /cities")
    pool = request.app.state.pool
    async with pool.acquire() as con:
        rows = await con.fetch(
            "SELECT name FROM cities ORDER BY name"
        )
        print(f"DEBUG: Found {len(rows)} cities")
        return {"cities": [row["name"] for row in rows]}


# ==============================================================
# ПОИСК (POST /search)
# Фронт: main.js → loadAllProperties() / filter.php
# Ответ: { results: [ { id, name, type, price_per_night, address, location, image_url, description } ] }
# ==============================================================

@app.post("/search")
async def search(request: Request):
    print(f"DEBUG: Search request. DB_URL from env: {os.getenv('DB_URL')}")
    pool = request.app.state.pool

    try:
        data = await request.json()
    except Exception:
        data = {}

    conditions = ["1=1"] # Показываем все, включая те, что на модерации
    params = []
    i = 1

    if data.get("location"):
        conditions.append(f"c.name ILIKE ${i}")
        params.append(f"%{data['location']}%")
        i += 1

    if data.get("type"):
        conditions.append(f"rt.name = ${i}")
        params.append(data["type"])
        i += 1

    if data.get("min_price") is not None:
        conditions.append(f"r.price_per_night >= ${i}")
        params.append(float(data["min_price"]))
        i += 1

    if data.get("max_price") is not None:
        conditions.append(f"r.price_per_night <= ${i}")
        params.append(float(data["max_price"]))
        i += 1

    # Проверка доступности по датам
    if data.get("date_from") and data.get("date_to"):
        conditions.append(f"""
            r.id NOT IN (
                SELECT resource_id FROM bookings
                WHERE status NOT IN ('CANCELLED')
                AND NOT (end_time <= ${i} OR start_time >= ${i+1})
            )
        """)
        try:
            params.append(parse_date(data["date_from"]))
            params.append(parse_date(data["date_to"]))
        except ValueError:
            pass
        i += 2

    where = "WHERE " + " AND ".join(conditions)

    async with pool.acquire() as con:
        rows = await con.fetch(
            f"""
            SELECT r.id, r.name, rt.name as type, r.description,
                   r.address, c.name as location, r.price_per_night, r.image_url,
                   r.area, r.guests, r.bedrooms, r.is_active,
                   COALESCE((SELECT array_agg(a.name) FROM resource_amenities ra JOIN amenities a ON ra.amenity_id = a.id WHERE ra.resource_id = r.id), '{{}}') as amenities,
                   COUNT(rv.id)::int AS review_count,
                   COALESCE(ROUND(AVG(rv.rating)::numeric, 1), 0)::float AS avg_rating
            FROM resources r
            LEFT JOIN reviews rv ON rv.resource_id = r.id
            LEFT JOIN cities c ON r.city_id = c.id
            LEFT JOIN resource_types rt ON r.type_id = rt.id
            {where}
            GROUP BY r.id, c.name, rt.name
            ORDER BY r.id
            LIMIT 50
            """,
            *params
        )
        results = []
        for row in rows:
            d = dict(row)
            d["image_url"] = map_image_url(d.get("image_url"), d["id"])
            if d.get("price_per_night"):
                d["price_per_night"] = float(d["price_per_night"])
            results.append(d)
        return {"results": results}


# ==============================================================
# РЕГИСТРАЦИЯ (POST /register)
# ==============================================================

@app.post("/register")
async def check_register(request: Request):
    pool = request.app.state.pool
    data = await request.json()

    async with pool.acquire() as con:
        existing = await con.fetch(
            "SELECT email FROM users WHERE email = $1", data["email"]
        )
        if existing and len(existing) > 0:
            return {"message": "почта занята"}

        salt = bcrypt.gensalt()
        password_hash = bcrypt.hashpw(data["password"].encode("utf8"), salt).decode("utf8")
        salt_str = salt.decode("utf8")

        try:
            await con.execute(
                """INSERT INTO users (email, password_hash, salt, name, surname, role, created_at)
                   VALUES ($1, $2, $3, $4, $5, $6, $7)""",
                data["email"], password_hash, salt_str,
                data.get("name", ""), data.get("surname", ""), "user", date.today()
            )
            return {"message": "ok"}
        except Exception as e:
            return {"message": f"ошибка регистрации: {str(e)}"}


# ==============================================================
# ВХОД (POST /login)
# ==============================================================

@app.post("/login")
async def check_login(request: Request):
    pool = request.app.state.pool
    data = await request.json()

    async with pool.acquire() as con:
        result = await con.fetch(
            "SELECT id, email, password_hash, role, name, surname, phone, passport FROM users WHERE email = $1",
            data["email"]
        )

        if not result or len(result) == 0:
            return {"message": "пользователь не найден"}

        stored_hash = result[0]["password_hash"]
        # Убираем артефакт bytes-представления если есть
        if stored_hash.startswith("b'") or stored_hash.startswith('b"'):
            stored_hash = stored_hash[2:-1]

        try:
            if bcrypt.checkpw(data["password"].encode("utf8"), stored_hash.encode("utf8")):
                # Расшифровываем паспорт для сессии если есть
                passport = decrypt_data(result[0].get("passport", ""))
                
                return {
                    "success": "true",
                    "id": result[0]["id"],
                    "redirect": "index.php",
                    "message": "вход успешен",
                    "role": result[0]["role"],
                    "name": result[0].get("name", ""),
                    "surname": result[0].get("surname", ""),
                    "email": result[0]["email"],
                    "phone": result[0].get("phone", ""),
                    "passport": passport
                }
            else:
                return {"message": "неправильный логин или пароль"}
        except Exception as e:
            print(f"Bcrypt check error: {e}")
            return {"message": "неправильный логин или пароль"}


# ==============================================================
# ПРОФИЛЬ ПОЛЬЗОВАТЕЛЯ (GET /users/me, POST /users/update)
# ==============================================================

@app.get("/users/me")
async def get_my_profile(user_id: int, request: Request):
    pool = request.app.state.pool
    async with pool.acquire() as con:
        row = await con.fetchrow(
            "SELECT id, email, name, surname, phone, passport, role FROM users WHERE id = $1",
            user_id
        )
        if not row:
            raise HTTPException(status_code=404, detail="Пользователь не найден")
        
        d = dict(row)
        if d.get("passport"):
            d["passport"] = decrypt_data(d["passport"])
        return d

@app.post("/users/update")
async def update_profile(request: Request):
    pool = request.app.state.pool
    data = await request.json()
    user_id = data.get("id")
    if not user_id:
        return {"error": "user_id required"}
    
    fields = []
    values = []
    i = 1
    
    for key in ["name", "surname", "phone", "passport"]:
        if key in data:
            val = data[key]
            if key == "passport":
                val = encrypt_data(val)
            fields.append(f"{key} = ${i}")
            values.append(val)
            i += 1
            
    if not fields:
        return {"message": "no fields to update"}
        
    values.append(int(user_id))
    query = f"UPDATE users SET {', '.join(fields)} WHERE id = ${i}"
    
    async with pool.acquire() as con:
        await con.execute(query, *values)
        return {"success": True, "message": "Профиль обновлен"}


# ==============================================================
# ИЗБРАННОЕ (GET /favorites, POST /favorites/toggle)
# ==============================================================

@app.get("/favorites")
async def get_favorites(user_id: int, request: Request):
    pool = request.app.state.pool
    async with pool.acquire() as con:
        rows = await con.fetch(
            """SELECT r.*, COUNT(rv.id)::int AS review_count,
                      COALESCE(ROUND(AVG(rv.rating)::numeric, 1), 0)::float AS avg_rating
               FROM resources r
               JOIN favorites f ON r.id = f.resource_id
               LEFT JOIN reviews rv ON rv.resource_id = r.id
               WHERE f.user_id = $1
               GROUP BY r.id""",
            user_id
        )
        results = []
        for row in rows:
            d = dict(row)
            d["image_url"] = map_image_url(d.get("image_url"), d["id"])
            # Приводим Decimal к float для JSON
            if d.get("price_per_night"):
                d["price_per_night"] = float(d["price_per_night"])
            results.append(d)
        return {"results": results}

@app.post("/favorites/toggle")
async def toggle_favorite(request: Request):
    pool = request.app.state.pool
    data = await request.json()
    user_id = data.get("user_id")
    resource_id = data.get("resource_id")
    
    if not user_id or not resource_id:
        return {"error": "user_id and resource_id required"}
        
    async with pool.acquire() as con:
        exists = await con.fetchval(
            "SELECT 1 FROM favorites WHERE user_id = $1 AND resource_id = $2",
            int(user_id), int(resource_id)
        )
        
        if exists:
            await con.execute(
                "DELETE FROM favorites WHERE user_id = $1 AND resource_id = $2",
                int(user_id), int(resource_id)
            )
            return {"status": "removed", "success": True}
        else:
            await con.execute(
                "INSERT INTO favorites (user_id, resource_id) VALUES ($1, $2)",
                int(user_id), int(resource_id)
            )
            return {"status": "added", "success": True}


# ==============================================================
# ЗАГРУЗКА ФАЙЛОВ (POST /upload)
# ==============================================================

@app.post("/upload")
async def upload_image(file: UploadFile = File(...)):
    # Путь для сохранения (внутри Docker /img/property)
    # Корень проекта примонтирован как /img
    upload_dir = "/img/property"
    print(f"DEBUG UPLOAD: Target dir: {upload_dir}")
    
    try:
        os.makedirs(upload_dir, exist_ok=True)
        print(f"DEBUG UPLOAD: Directory ensured: {os.path.exists(upload_dir)}")
        
        file_ext = os.path.splitext(file.filename)[1]
        unique_filename = f"{uuid.uuid4()}{file_ext}"
        file_path = os.path.join(upload_dir, unique_filename)
        print(f"DEBUG UPLOAD: Full file path: {file_path}")
        
        with open(file_path, "wb") as buffer:
            shutil.copyfileobj(file.file, buffer)
        
        print(f"DEBUG UPLOAD: File saved successfully: {os.path.exists(file_path)}")
        return {"url": f"../img/property/{unique_filename}"}
    except Exception as e:
        print(f"DEBUG UPLOAD ERROR: {str(e)}")
        import traceback
        traceback.print_exc()
        raise HTTPException(status_code=500, detail=str(e))


# ==============================================================
# СОЗДАНИЕ РЕСУРСА (POST /resources)
# ==============================================================

@app.post("/resources")
async def create_resource(request: Request):
    pool = request.app.state.pool
    try:
        data = await request.json()
        
        # Обработка вложенных данных из rent.php (если есть)
        details = data.get("details", {})
        area = data.get("area") or details.get("area") or 0
        guests = data.get("guests") or details.get("guests") or 0
        bedrooms = data.get("bedrooms") or details.get("bedrooms") or 0
        amenities = data.get("amenities") or details.get("amenities") or []
        
        async with pool.acquire() as con:
            # Получаем или создаем город
            loc_name = data.get("location", "")
            if loc_name:
                city_id = await con.fetchval(
                    "INSERT INTO cities (name) VALUES ($1) ON CONFLICT (name) DO UPDATE SET name=EXCLUDED.name RETURNING id",
                    loc_name
                )
            else:
                city_id = None
                
            # Получаем или создаем тип
            type_name = data.get("type", "apartment")
            type_id = await con.fetchval(
                "INSERT INTO resource_types (name, display_name) VALUES ($1, $1) ON CONFLICT (name) DO UPDATE SET name=EXCLUDED.name RETURNING id",
                type_name
            )

            result = await con.fetchrow(
               """INSERT INTO resources (name, description, price_per_night, is_active, address, image_url, area, guests, bedrooms, city_id, type_id, type, location)
                  VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13) RETURNING id""",
               data.get("name", "Без названия"),
               data.get("description", ""),
               float(data.get("price_per_night", 0)),
               data.get("is_active", True),
               data.get("address", ""),
               data.get("image_url", None),
               int(area),
               int(guests),
               int(bedrooms),
               city_id,
               type_id,
               type_name,
               loc_name
            )
            res_id = result["id"]
            
            # Вставка удобств
            if amenities:
                for am_name in amenities:
                    am_id = await con.fetchval(
                        "INSERT INTO amenities (name) VALUES ($1) ON CONFLICT (name) DO UPDATE SET name=EXCLUDED.name RETURNING id",
                        am_name
                    )
                    await con.execute(
                        "INSERT INTO resource_amenities (resource_id, amenity_id) VALUES ($1, $2) ON CONFLICT DO NOTHING",
                        res_id, am_id
                    )
                    
            return {"id": res_id, "message": "Объект успешно добавлен"}
    except Exception as e:
        import traceback
        traceback.print_exc()
        return {"error": str(e)}


# ==============================================================
# БРОНИРОВАНИЕ (POST /bookings)
# ==============================================================

@app.post("/bookings")
async def create_booking(request: Request):
    pool = request.app.state.pool
    try:
        data = await request.json()

        start_str = data.get("start_time") or data.get("checkin")
        end_str = data.get("end_time") or data.get("checkout")
        if not start_str or not end_str:
            return {"error": "start_time и end_time обязательны"}

        start_time = parse_date(start_str)
        end_time = parse_date(end_str)
        price_per_night = float(data.get("price_per_night", 0))
        resource_id = int(data.get("resource_id", 1))
        user_id = int(data.get("user_id", 1))
        comment = data.get("comment", "")
        adults = int(data.get("adults", 0))
        children = int(data.get("children", 0))
        name = data.get("name", "")
        email = data.get("email", "")
        phone = data.get("phone", "")
        passport = data.get("passport", "")
        
        # Шифруем паспортные данные
        encrypted_passport = encrypt_data(passport)

        async with pool.acquire() as con:
            # Проверяем доступность. Конфликтуем только с оплаченными, подтвержденными 
            # или свежими (созданными < 30 мин назад) бронированиями других пользователей.
            # Бронирования со статусом CANCELLED или COMPLETED игнорируем.
            conflict = await con.fetchrow(
                """SELECT id, status, user_id FROM bookings
                   WHERE resource_id = $1 
                   AND status NOT IN ('CANCELLED', 'COMPLETED')
                   AND (
                       status != 'CREATED' 
                       OR created_at > NOW() - INTERVAL '30 minutes'
                   )
                   AND NOT (end_time <= $2 OR start_time >= $3)
                   LIMIT 1""",
                resource_id, start_time, end_time
            )
            
            if conflict:
                # Если конфликт с собственным CREATED бронированием — разрешаем "перезаписать" (просто создаем новое)
                if conflict["status"] == "CREATED" and conflict["user_id"] == user_id:
                    print(f"DEBUG: Found own old CREATED booking {conflict['id']}, allowing new one.")
                else:
                    return {"error": "Объект уже забронирован на эти даты", "success": False}

            # Вставка в bookings (без персональных данных)
            result = await con.fetchrow(
                """INSERT INTO bookings (user_id, resource_id, start_time, end_time, status, price, comment, adults, children)
                   VALUES ($1, $2, $3, $4, 'CREATED', $5, $6, $7, $8) RETURNING id""",
                user_id, resource_id, start_time, end_time, price_per_night, comment, adults, children
            )
            
            # Вставка в guest_profiles
            await con.execute(
                """INSERT INTO guest_profiles (booking_id, name, email, phone, passport)
                   VALUES ($1, $2, $3, $4, $5)""",
                result["id"], name, email, phone, encrypted_passport
            )
            
            # Авто-обновление профиля пользователя (сохранение "по аккаунту")
            await con.execute(
                """UPDATE users SET phone = COALESCE(NULLIF($1, ''), phone), 
                                   passport = COALESCE(NULLIF($2, ''), passport)
                   WHERE id = $3""",
                phone, encrypted_passport, user_id
            )
            
            # Логирование
            await con.execute(
                """INSERT INTO audit_logs (action, user_id, resource_id, details)
                   VALUES ($1, $2, $3, $4)""",
                "CREATE_BOOKING", user_id, resource_id, f"Бронирование #{result['id']} создано (гость: {name})"
            )
            
            return {"id": result["id"], "message": "Бронирование успешно создано", "success": True}
    except Exception as e:
        import traceback
        traceback.print_exc()
        error_msg = str(e)
        # Если это ошибка requests (через которую работает SDK), попробуем вытащить детали
        try:
            if hasattr(e, 'response') and e.response is not None:
                error_msg += f" | Details: {e.response.text}"
        except:
            pass
        return {"error": error_msg}


# ==============================================================
# ADMIN API (POST /admin_api)
# ==============================================================

@app.post("/admin_api")
async def admin_api(request: Request):
    pool = request.app.state.pool
    try:
        data = await request.json()
        action = data.get("action")
        table = data.get("table", "")

        allowed_tables = ["users", "resources", "bookings"]
        if table not in allowed_tables:
            return {"error": "invalid table"}

        async with pool.acquire() as con:
            if action == "get_all":
                if table == "users":
                    rows = await con.fetch(
                        "SELECT id, email, name, surname, role, created_at FROM users ORDER BY id"
                    )
                elif table == "resources":
                    rows = await con.fetch(
                        """SELECT r.id, r.name, rt.name as type, r.price_per_night, c.name as location, r.address, r.is_active, r.area, r.guests, r.bedrooms, r.image_url,
                                  COALESCE((SELECT array_agg(a.name) FROM resource_amenities ra JOIN amenities a ON ra.amenity_id = a.id WHERE ra.resource_id = r.id), '{}') as amenities
                           FROM resources r 
                           LEFT JOIN cities c ON r.city_id = c.id
                           LEFT JOIN resource_types rt ON r.type_id = rt.id
                           ORDER BY r.id"""
                    )
                elif table == "bookings":
                    rows = await con.fetch(
                        """SELECT b.id, b.user_id, b.resource_id,
                                  b.start_time, b.end_time, b.status, b.price,
                                  b.comment, b.adults, b.children,
                                  gp.name, gp.email, gp.phone, gp.passport,
                                  u.email as user_email,
                                  COALESCE(u.name || ' ' || u.surname, u.email) as user_name,
                                  r.name as resource_name
                           FROM bookings b
                           LEFT JOIN guest_profiles gp ON b.id = gp.booking_id
                           LEFT JOIN users u ON b.user_id = u.id
                           LEFT JOIN resources r ON b.resource_id = r.id
                           ORDER BY b.id DESC"""
                    )
                else:
                    return {"error": "invalid table"}

                results = []
                for row in rows:
                    d = dict(row)
                    # Расшифровка паспорта если есть
                    if table == "bookings" and d.get("passport"):
                        d["passport"] = decrypt_data(d["passport"])
                    
                    for k, v in d.items():
                        if hasattr(v, 'isoformat'):
                            d[k] = v.isoformat()
                    results.append(d)
                return {"results": results}

            elif action == "delete":
                item_id = data.get("id")
                if not item_id:
                    return {"error": "id required"}
                await con.execute(f"DELETE FROM {table} WHERE id = $1", int(item_id))
                return {"success": True}

            elif action == "update":
                item_id = data.get("id")
                fields = data.get("fields", {})
                if not item_id or not fields:
                    return {"error": "id and fields required"}

                # Специальная обработка для таблицы resources: маппинг строк в ID
                if table == "resources":
                    if "location" in fields:
                        loc_name = fields.pop("location")
                        if loc_name:
                            city_id = await con.fetchval(
                                "INSERT INTO cities (name) VALUES ($1) ON CONFLICT (name) DO UPDATE SET name=EXCLUDED.name RETURNING id",
                                loc_name
                            )
                            fields["city_id"] = city_id
                    if "type" in fields:
                        type_name = fields.pop("type")
                        if type_name:
                            type_id = await con.fetchval(
                                "INSERT INTO resource_types (name, display_name) VALUES ($1, $1) ON CONFLICT (name) DO UPDATE SET name=EXCLUDED.name RETURNING id",
                                type_name
                            )
                            fields["type_id"] = type_id

                set_clauses = []
                params = []
                for idx, (k, v) in enumerate(fields.items()):
                    if not k.isidentifier():
                        continue
                    
                    # Специальная обработка amenities
                    if k == "amenities" and isinstance(v, list):
                        v = json.dumps(v, ensure_ascii=False)
                    
                    set_clauses.append(f"{k} = ${idx + 1}")
                    params.append(v)
                params.append(int(item_id))

                query = f"UPDATE {table} SET {', '.join(set_clauses)} WHERE id = ${len(params)}"
                await con.execute(query, *params)
                return {"success": True}

        return {"error": "invalid action"}
    except Exception as e:
        import traceback
        traceback.print_exc()
        error_msg = str(e)
        # Если это ошибка requests (через которую работает SDK), попробуем вытащить детали
        try:
            if hasattr(e, 'response') and e.response is not None:
                error_msg += f" | Details: {e.response.text}"
        except:
            pass
        return {"error": error_msg}



# ==============================================================
# ОТЗЫВЫ (GET /reviews/{resource_id}, POST /reviews)
# ==============================================================

@app.get("/reviews/{resource_id}")
async def get_reviews(resource_id: int, request: Request):
    pool = request.app.state.pool
    async with pool.acquire() as con:
        rows = await con.fetch(
            """SELECT r.id, 
                      COALESCE(NULLIF(u.name || ' ' || u.surname, ' '), r.author_name) as author_name,
                      r.rating, r.comment, r.created_at, r.user_id
               FROM reviews r
               LEFT JOIN users u ON r.user_id = u.id
               WHERE r.resource_id = $1
               ORDER BY r.created_at DESC""",
            resource_id
        )
        results = []
        for row in rows:
            d = dict(row)
            if d.get("created_at"):
                d["created_at"] = d["created_at"].isoformat()
            results.append(d)
        return {"reviews": results}


@app.post("/reviews")
async def add_review(request: Request):
    pool = request.app.state.pool
    try:
        data = await request.json()
        resource_id = int(data.get("resource_id", 0))
        author_name = data.get("author_name", "Гость")
        rating = int(data.get("rating", 5))
        comment = data.get("comment", "")
        user_id = data.get("user_id")
        
        if not resource_id:
            return {"error": "resource_id required"}
        async with pool.acquire() as con:
            await con.execute(
                """INSERT INTO reviews (resource_id, author_name, rating, comment, user_id)
                   VALUES ($1, $2, $3, $4, $5)""",
                resource_id, author_name, rating, comment, int(user_id) if user_id else None
            )
        return {"message": "Отзыв добавлен", "success": True}
    except Exception as e:
        return {"error": str(e)}


# ==============================================================
# СТАТИСТИКА (GET /stats)
# ==============================================================

@app.get("/stats")
async def get_stats(request: Request):
    pool = request.app.state.pool
    async with pool.acquire() as con:
        users_count = await con.fetchval("SELECT COUNT(*) FROM users")
        resources_count = await con.fetchval("SELECT COUNT(*) FROM resources WHERE is_active = TRUE")
        bookings_count = await con.fetchval("SELECT COUNT(*) FROM bookings")
        total_revenue = await con.fetchval("SELECT COALESCE(SUM(price), 0) FROM bookings WHERE status != 'CANCELLED'")
        try:
            reviews_count = await con.fetchval("SELECT COUNT(*) FROM reviews")
        except Exception:
            reviews_count = 0
        return {
            "users": users_count,
            "resources": resources_count,
            "bookings": bookings_count,
            "revenue": float(total_revenue),
            "reviews": reviews_count
        }


# ==============================================================
# ПОЛУЧИТЬ ОДИН РЕСУРС (GET /resources/{id})
# ==============================================================

@app.get("/resources/{resource_id}")
async def get_resource(request: Request, resource_id: int):
    pool = request.app.state.pool
    async with pool.acquire() as con:
        row = await con.fetchrow(
            """SELECT r.*, 
                      COALESCE((SELECT array_agg(a.name) FROM resource_amenities ra JOIN amenities a ON ra.amenity_id = a.id WHERE ra.resource_id = r.id), '{}') as amenities
               FROM resources r WHERE r.id = $1""", 
            resource_id
        )
        if not row:
            raise HTTPException(status_code=404, detail="Объект не найден")
        
        d = dict(row)
        d["image_url"] = map_image_url(d.get("image_url"), d["id"])
        # Приводим Decimal к float
        if d.get("price_per_night"):
            d["price_per_night"] = float(d["price_per_night"])
        return d


# ==============================================================
# МОИ БРОНИРОВАНИЯ (GET /my-bookings)
# ==============================================================

@app.get("/my-bookings")
async def my_bookings(request: Request, user_id: int):
    pool = request.app.state.pool
    async with pool.acquire() as con:
        # Сначала пробуем обновить статусы для всех PENDING платежей этого пользователя
        pending_payments = await con.fetch(
            """SELECT p.id, p.external_id, b.id as booking_id 
               FROM payments p 
               JOIN bookings b ON p.booking_id = b.id 
               WHERE b.user_id = $1 AND p.status = 'PENDING'""",
            user_id
        )
        
        for pay in pending_payments:
            try:
                print(f"DEBUG: Checking YooKassa status for payment {pay['id']} (ext_id: {pay['external_id']})")
                payment_info = YooPayment.find_one(pay["external_id"])
                print(f"DEBUG: YooKassa status for {pay['id']} is {payment_info.status}")
                if payment_info.status in ["succeeded", "waiting_for_capture"]:
                    print(f"DEBUG: Updating payment {pay['id']} and booking {pay['booking_id']} to SUCCESS/PAID (status: {payment_info.status})")
                    await con.execute("UPDATE payments SET status = 'SUCCESS' WHERE id = $1", pay["id"])
                    await con.execute("UPDATE bookings SET status = 'PAID' WHERE id = $1", pay["booking_id"])
            except Exception as e:
                print(f"DEBUG: Error checking payment {pay['id']}: {str(e)}")
                pass # Игнорируем ошибки связи с ЮKassa

        # Теперь возвращаем актуальный список
        rows = await con.fetch(
            """SELECT b.id, b.status, b.start_time, b.end_time, b.price, b.created_at,
                      b.adults, b.children, b.comment, b.passport,
                      r.id as resource_id, r.name as resource_name, r.address, r.location, r.image_url
               FROM bookings b
               JOIN resources r ON b.resource_id = r.id
               WHERE b.user_id = $1
               ORDER BY b.created_at DESC LIMIT 20""",
            user_id
        )
        results = []
        for row in rows:
            d = dict(row)
            # Расшифровка паспорта для владельца брони
            if d.get("passport"):
                d["passport"] = decrypt_data(d["passport"])

            for k, v in d.items():
                if hasattr(v, 'isoformat'):
                    d[k] = v.isoformat()
            
            # Маппинг картинки
            d["image_url"] = map_image_url(d.get("image_url"), d["resource_id"])
            results.append(d)
        return {"bookings": results}


# ==============================================================
# ПЛАТЕЖИ (POST /payments/create, POST /payments/confirm)
# ==============================================================

@app.post("/payments/create")
async def create_payment(request: Request):
    pool = request.app.state.pool
    try:
        data = await request.json()
        booking_id = int(data.get("booking_id"))
        amount = float(data.get("amount"))

        # Формируем URL возврата
        env_frontend = os.getenv("FRONTEND_URL")
        origin_header = request.headers.get("origin")
        referer_header = request.headers.get("referer")
        
        if env_frontend:
            frontend_base = env_frontend.rstrip("/")
        elif origin_header:
            frontend_base = origin_header.rstrip("/")
        elif referer_header:
            from urllib.parse import urlparse
            parsed = urlparse(referer_header)
            frontend_base = f"{parsed.scheme}://{parsed.netloc}"
        else:
            frontend_base = "http://localhost"

        # КРИТИЧЕСКИЙ ФИКС: Убираем /kursach или /front из конца, если они там есть.
        # В Docker фронтенд всегда в корне /.
        for suffix in ["/kursach", "/front"]:
            if frontend_base.endswith(suffix):
                frontend_base = frontend_base[:-len(suffix)]

        return_url = f"{frontend_base.rstrip('/')}/bookings.php"
        print(f"DEBUG PAYMENT: env_frontend='{env_frontend}', FINAL return_url='{return_url}'")
        
        # Проверяем, есть ли реальные ключи ЮKassa, иначе используем заглушку
        if not Configuration.account_id or Configuration.account_id == "YOUR_SHOP_ID":
            print(f"DEBUG PAYMENT: Using mock payment for booking {booking_id} because YooKassa keys are missing/invalid")
            mock_external_id = f"mock-{uuid.uuid4()}"
            async with pool.acquire() as con:
                # Сразу ставим SUCCESS в базе для заглушки
                result = await con.fetchrow(
                    """INSERT INTO payments (booking_id, amount, status, payment_method, external_id)
                       VALUES ($1, $2, 'SUCCESS', 'Mock', $3) RETURNING id""",
                    booking_id, amount, mock_external_id
                )
                # И сразу обновляем статус бронирования
                await con.execute("UPDATE bookings SET status = 'PAID' WHERE id = $1", booking_id)
                
            return {
                "id": result["id"],
                "confirmation_url": return_url, # Сразу редирект обратно на bookings.php
                "mock": True
            }

        # Создаем платеж в ЮKassa
        idempotence_key = str(uuid.uuid4())
        payment = YooPayment.create({
            "amount": {
                "value": f"{amount:.2f}",
                "currency": "RUB"
            },
            "confirmation": {
                "type": "redirect",
                "return_url": return_url
            },
            "capture": True,
            "description": f"Оплата бронирования №{booking_id}",
            "metadata": {
                "booking_id": booking_id
            }
        }, idempotence_key)
        
        external_id = payment.id
        confirmation_url = payment.confirmation.confirmation_url

        async with pool.acquire() as con:
            # Сохраняем ID платежа в нашей базе
            result = await con.fetchrow(
                """INSERT INTO payments (booking_id, amount, status, payment_method, external_id)
                   VALUES ($1, $2, 'PENDING', 'YooKassa', $3) RETURNING id""",
                booking_id, amount, external_id
            )
            
            return {
                "id": result["id"],
                "confirmation_url": confirmation_url
            }
    except Exception as e:
        import traceback
        traceback.print_exc()
        error_msg = str(e)
        # Если это ошибка requests (через которую работает SDK), попробуем вытащить детали
        try:
            if hasattr(e, 'response') and e.response is not None:
                error_msg += f" | Details: {e.response.text}"
        except:
            pass
        return {"error": error_msg}


@app.post("/payments/webhook")
async def yookassa_webhook(request: Request):
    """Эндпоинт для уведомлений от ЮKassa"""
    pool = request.app.state.pool
    try:
        data = await request.json()
        event = data.get("event")
        obj = data.get("object", {})
        
        if event == "payment.succeeded":
            external_id = obj.get("id")
            booking_id = int(obj.get("metadata", {}).get("booking_id", 0))

            async with pool.acquire() as con:
                # Обновляем статус платежа
                await con.execute(
                    "UPDATE payments SET status = 'SUCCESS', created_at = $1 WHERE external_id = $2",
                    datetime.now(), external_id
                )
                # Обновляем статус бронирования
                if booking_id:
                    await con.execute(
                        "UPDATE bookings SET status = 'PAID' WHERE id = $1",
                        booking_id
                    )
        return {"status": "ok"}
    except Exception as e:
        return {"error": str(e)}


@app.post("/payments/confirm")
async def confirm_payment(request: Request):
    pool = request.app.state.pool
    try:
        data = await request.json()
        payment_id = int(data.get("payment_id"))

        async with pool.acquire() as con:
            payment_row = await con.fetchrow(
                "SELECT external_id, booking_id FROM payments WHERE id = $1", payment_id
            )
            if not payment_row:
                return {"error": "Платеж не найден"}
            
            ext_id = payment_row["external_id"]
            
            # В тестовом режиме мы можем просто проверить статус через API ЮKassa
            try:
                payment_info = YooPayment.find_one(ext_id)
                if payment_info.status == "succeeded":
                    await con.execute(
                        "UPDATE payments SET status = 'SUCCESS', created_at = $1 WHERE id = $2",
                        datetime.now(), payment_id
                    )
                    await con.execute(
                        "UPDATE bookings SET status = 'PAID' WHERE id = $1",
                        payment_row["booking_id"]
                    )
                    return {"success": True, "message": "Оплата подтверждена"}
                else:
                    return {"success": False, "message": f"Статус в ЮKassa: {payment_info.status}"}
            except Exception as ye:
                return {"error": f"Ошибка ЮKassa: {str(ye)}"}
                
    except Exception as e:
        return {"error": str(e)}


if __name__ == '__main__':
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
