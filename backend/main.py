from fastapi import FastAPI, Request, Response, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import dotenv
import os
import asyncpg
from contextlib import asynccontextmanager
from datetime import datetime, date
import bcrypt
from ai_router import ai_router

'''
API бэкенд для PHP-фронта BRONIC.RU
БД: PostgreSQL, таблицы: users, resources, bookings, payments, messages, services
Фронт: PHP (XAMPP, порт 80) → бэк (FastAPI, порт 8000)
'''

dotenv.load_dotenv()


@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup — создаём пул соединений
    app.state.pool = await asyncpg.create_pool(
        dsn=os.getenv("DB_URL"),
        min_size=5,
        max_size=20
    )
    print("Database pool created")
    yield
    # Shutdown — закрываем пул
    await app.state.pool.close()
    print("Database pool closed")


app = FastAPI(lifespan=lifespan)

# CORS — разрешаем запросы с PHP-фронта (XAMPP, порт 80)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Подключаем AI-роутер
app.include_router(ai_router, prefix="/ai", tags=["AI"])


# ==============================================================
# ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
# ==============================================================

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
    pool = request.app.state.pool
    async with pool.acquire() as con:
        rows = await con.fetch(
            "SELECT DISTINCT location FROM resources WHERE is_active = TRUE AND location IS NOT NULL ORDER BY location"
        )
        return {"cities": [row["location"] for row in rows]}


# ==============================================================
# ПОИСК (POST /search)
# Фронт: main.js → loadAllProperties() / filter.php
# Ответ: { results: [ { id, name, type, base_price, address, location, image_url, description } ] }
# ==============================================================

@app.post("/search")
async def search(request: Request):
    pool = request.app.state.pool

    try:
        data = await request.json()
    except Exception:
        data = {}

    conditions = ["r.is_active = TRUE"]
    params = []
    i = 1

    if data.get("location"):
        conditions.append(f"r.location ILIKE ${i}")
        params.append(f"%{data['location']}%")
        i += 1

    if data.get("type"):
        conditions.append(f"r.type = ${i}")
        params.append(data["type"])
        i += 1

    if data.get("min_price") is not None:
        conditions.append(f"r.base_price >= ${i}")
        params.append(float(data["min_price"]))
        i += 1

    if data.get("max_price") is not None:
        conditions.append(f"r.base_price <= ${i}")
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
            SELECT r.id, r.name, r.type, r.description,
                   r.address, r.location, r.base_price, r.image_url,
                   COUNT(rv.id)::int AS review_count,
                   COALESCE(ROUND(AVG(rv.rating)::numeric, 1), 0)::float AS avg_rating
            FROM resources r
            LEFT JOIN reviews rv ON rv.resource_id = r.id
            {where}
            GROUP BY r.id
            ORDER BY r.id
            LIMIT 50
            """,
            *params
        )
        results = []
        for row in rows:
            d = dict(row)
            print(f"DEBUG: Property {d['id']} has {d['review_count']} reviews in DB")
            # FORCE LOCAL PATHS
            if d.get("image_url"):
                img = d["image_url"]
                if "unsplash.com" in img or "loremflickr.com" in img or "placeimg.com" in img:
                    # Try to map to local based on ID or name
                    mapping = {
                        1: "metro-plus.png",
                        2: "lesnau-skazka.webp",
                        3: "komnata-arbat.jpg",
                        4: "kotedzh-luxery.webp",
                        5: "studia.jpg",
                        6: "dacha-u-ozera.jpg",
                        8: "metro-plus.png"
                    }
                    fname = mapping.get(d["id"], "metro-plus.png")
                    d["image_url"] = f"../img/property/{fname}"
            results.append(d)
        return {"results": results}


# ==============================================================
# РЕГИСТРАЦИЯ (POST /register)
# Фронт: register.php
# Ответ при успехе: { message: "ok" }
# Ответ при ошибке: { message: "почта занята" }
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
# Фронт: login.php
# Ответ при успехе: { success: "true", message: "вход успешен", role, name, surname, email }
# Ответ при ошибке: { message: "пользователь не найден" / "неправильный логин или пароль" }
# ==============================================================

@app.post("/login")
async def check_login(request: Request):
    pool = request.app.state.pool
    data = await request.json()

    async with pool.acquire() as con:
        result = await con.fetch(
            "SELECT id, email, password_hash, role, name, surname FROM users WHERE email = $1",
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
                return {
                    "success": "true",
                    "redirect": "index.php",
                    "message": "вход успешен",
                    "role": result[0]["role"],
                    "name": result[0].get("name", ""),
                    "surname": result[0].get("surname", ""),
                    "email": result[0]["email"]
                }
            else:
                return {"message": "неправильный логин или пароль"}
        except Exception as e:
            print(f"Bcrypt check error: {e}")
            return {"message": "неправильный логин или пароль"}


# ==============================================================
# СОЗДАНИЕ РЕСУРСА (POST /resources)
# Фронт: admin.php
# ==============================================================

@app.post("/resources")
async def create_resource(request: Request):
    pool = request.app.state.pool
    try:
        data = await request.json()
        async with pool.acquire() as con:
            result = await con.fetchrow(
                """INSERT INTO resources (name, type, description, base_price, is_active, address, location, image_url)
                   VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id""",
                data.get("name", "Без названия"),
                data.get("type", "appartment"),
                data.get("description", ""),
                float(data.get("base_price", 0)),
                data.get("is_active", True),
                data.get("address", ""),
                data.get("location", ""),
                data.get("image_url", None)
            )
            return {"id": result["id"], "message": "Объект успешно добавлен"}
    except Exception as e:
        return {"error": str(e)}


# ==============================================================
# БРОНИРОВАНИЕ (POST /bookings)
# Фронт: booking.php
# Ожидает: { resource_id, user_id, start_time/checkin, end_time/checkout, price }
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
        price = float(data.get("price") or data.get("price_per_night", 0))
        resource_id = int(data.get("resource_id", 1))
        user_id = int(data.get("user_id", 1))

        async with pool.acquire() as con:
            # Проверяем доступность
            conflict = await con.fetchrow(
                """SELECT id FROM bookings
                   WHERE resource_id = $1 AND status != 'CANCELLED'
                   AND NOT (end_time <= $2 OR start_time >= $3)""",
                resource_id, start_time, end_time
            )
            if conflict:
                return {"error": "Объект уже забронирован на эти даты", "success": False}

            result = await con.fetchrow(
                """INSERT INTO bookings (user_id, resource_id, start_time, end_time, status, price)
                   VALUES ($1, $2, $3, $4, 'CREATED', $5) RETURNING id""",
                user_id, resource_id, start_time, end_time, price
            )
            return {"id": result["id"], "message": "Бронирование успешно создано", "success": True}
    except Exception as e:
        import traceback
        traceback.print_exc()
        return {"error": str(e)}


# ==============================================================
# ADMIN API (POST /admin_api)
# Фронт: admin.php, booking.php (получение user_id по email)
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
                        "SELECT id, name, type, base_price, location, address, is_active FROM resources ORDER BY id"
                    )
                elif table == "bookings":
                    rows = await con.fetch(
                        """SELECT b.id, b.user_id, b.resource_id,
                                  b.start_time, b.end_time, b.status, b.price,
                                  u.email as user_email,
                                  COALESCE(u.name || ' ' || u.surname, u.email) as user_name,
                                  r.name as resource_name
                           FROM bookings b
                           LEFT JOIN users u ON b.user_id = u.id
                           LEFT JOIN resources r ON b.resource_id = r.id
                           ORDER BY b.id DESC"""
                    )
                else:
                    return {"error": "invalid table"}

                results = []
                for row in rows:
                    d = dict(row)
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

                set_clauses = []
                params = []
                for idx, (k, v) in enumerate(fields.items()):
                    if not k.isidentifier():
                        continue
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
        return {"error": str(e)}



# ==============================================================
# ОТЗЫВЫ (GET /reviews/{resource_id}, POST /reviews)
# ==============================================================

@app.get("/reviews/{resource_id}")
async def get_reviews(resource_id: int, request: Request):
    pool = request.app.state.pool
    async with pool.acquire() as con:
        rows = await con.fetch(
            """SELECT id, author_name, rating, comment, created_at
               FROM reviews WHERE resource_id = $1
               ORDER BY created_at DESC""",
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
        if not resource_id:
            return {"error": "resource_id required"}
        async with pool.acquire() as con:
            await con.execute(
                """INSERT INTO reviews (resource_id, author_name, rating, comment)
                   VALUES ($1, $2, $3, $4)""",
                resource_id, author_name, rating, comment
            )
        return {"message": "Отзыв добавлен", "success": True}
    except Exception as e:
        return {"error": str(e)}


# ==============================================================
# СТАТИСТИКА (GET /stats) — для админки
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
# Фронт: rent.php / страница детали объекта
# ==============================================================

@app.get("/resources/{resource_id}")
async def get_resource(request: Request, resource_id: int):
    pool = request.app.state.pool
    async with pool.acquire() as con:
        row = await con.fetchrow(
            "SELECT * FROM resources WHERE id = $1", resource_id
        )
        if not row:
            raise HTTPException(status_code=404, detail="Объект не найден")
        
        d = dict(row)
        if d.get("image_url") and ("http" in d["image_url"]):
            mapping = {
                1: "metro-plus.png",
                2: "lesnau-skazka.webp",
                3: "komnata-arbat.jpg",
                4: "kotedzh-luxery.webp",
                5: "studia.jpg",
                6: "dacha-u-ozera.jpg",
                8: "metro-plus.png"
            }
            fname = mapping.get(d["id"], "metro-plus.png")
            d["image_url"] = f"../img/property/{fname}"
            
        return d


# ==============================================================
# МОИ БРОНИРОВАНИЯ (GET /my-bookings)
# Фронт: bookings.php
# ==============================================================

@app.get("/my-bookings")
async def my_bookings(request: Request, user_id: int):
    pool = request.app.state.pool
    async with pool.acquire() as con:
        rows = await con.fetch(
            """SELECT b.id, b.status, b.start_time, b.end_time, b.price, b.created_at,
                      r.name as resource_name, r.address, r.location, r.image_url
               FROM bookings b
               JOIN resources r ON b.resource_id = r.id
               WHERE b.user_id = $1
               ORDER BY b.created_at DESC LIMIT 20""",
            user_id
        )
        results = []
        for row in rows:
            d = dict(row)
            for k, v in d.items():
                if hasattr(v, 'isoformat'):
                    d[k] = v.isoformat()
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

        async with pool.acquire() as con:
            # Создаем запись в таблице payments
            result = await con.fetchrow(
                """INSERT INTO payments (booking_id, amount, status, payment_method)
                   VALUES ($1, $2, 'PENDING', 'YooKassa') RETURNING id""",
                booking_id, amount
            )
            payment_id = result["id"]
            
            # В реальной жизни тут был бы запрос к API ЮKassa
            # Мы возвращаем "confirmation_url" на наш фейковый терминал
            return {
                "id": payment_id,
                "confirmation_url": f"payment.php?payment_id={payment_id}&amount={amount}&booking_id={booking_id}"
            }
    except Exception as e:
        return {"error": str(e)}


@app.post("/payments/confirm")
async def confirm_payment(request: Request):
    pool = request.app.state.pool
    try:
        data = await request.json()
        payment_id = int(data.get("payment_id"))

        async with pool.acquire() as con:
            # Находим платеж
            payment = await con.fetchrow(
                "SELECT booking_id, amount FROM payments WHERE id = $1", payment_id
            )
            if not payment:
                return {"error": "Платеж не найден"}

            # Обновляем статус платежа
            await con.execute(
                "UPDATE payments SET status = 'SUCCESS', created_at = $1 WHERE id = $2",
                datetime.now(), payment_id
            )

            # Обновляем статус бронирования
            await con.execute(
                "UPDATE bookings SET status = 'PAID' WHERE id = $1",
                payment["booking_id"]
            )

            return {"success": True, "message": "Оплата подтверждена"}
    except Exception as e:
        return {"error": str(e)}


if __name__ == '__main__':
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)