import asyncio
import asyncpg
import os
import random
import bcrypt
from datetime import datetime, timedelta
import dotenv

dotenv.load_dotenv()

async def seed():
    dsn = os.getenv("DB_URL")
    print(f"Connecting to: {dsn}")
    con = await asyncpg.connect(dsn=dsn)
    
    # 1. Сброс данных (опционально, но лучше добавить новые к существующим)
    # await con.execute("TRUNCATE TABLE audit_logs, guest_profiles, resource_amenities, reviews, bookings, payments, users, resources CASCADE")
    
    # 2. Обеспечиваем наличие колонок в users
    print("Ensuring users columns exist...")
    await con.execute("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(50)")
    await con.execute("ALTER TABLE users ADD COLUMN IF NOT EXISTS passport TEXT")
    
    # 2. Список городов и типов
    cities = ["Москва", "Санкт-Петербург", "Сочи", "Казань", "Екатеринбург", "Нижний Новгород", "Калининград", "Владивосток"]
    types = ["apartment", "house", "room", "hotel"]
    
    # 3. Создаем пользователей
    print("Seeding users...")
    names = ["Александр", "Мария", "Дмитрий", "Елена", "Иван", "Анна", "Сергей", "Ольга", "Михаил", "Наталья"]
    surnames = ["Иванов", "Петрова", "Смирнов", "Кузнецова", "Попов", "Васильева", "Соколов", "Михайлова", "Новиков", "Федорова"]
    
    user_ids = []
    salt = bcrypt.gensalt()
    pwd_hash = bcrypt.hashpw("password123".encode("utf8"), salt).decode("utf8")
    
    for i in range(25):
        name = random.choice(names)
        surname = random.choice(surnames)
        email = f"user{i+10}@example.com"
        
        # Проверяем существование
        uid = await con.fetchval("SELECT id FROM users WHERE email = $1", email)
        if not uid:
            uid = await con.fetchval(
                """INSERT INTO users (email, password_hash, salt, name, surname, role, phone)
                   VALUES ($1, $2, $3, $4, $5, 'user', $6) RETURNING id""",
                email, pwd_hash, salt.decode("utf8"), name, surname, f"+7999{random.randint(1000000, 9999999)}"
            )
        user_ids.append(uid)
    
    # 4. Удобства (Amenities)
    print("Seeding amenities...")
    amenities = [
        ("Wi-Fi", "bi-wifi"), ("Парковка", "bi-p-circle"), ("Кухня", "bi-egg-fried"),
        ("Кондиционер", "bi-snow"), ("ТВ", "bi-tv"), ("Сейф", "bi-safe"),
        ("Бассейн", "bi-water"), ("Баня", "bi-thermometer-half"), ("Мангал", "bi-fire"),
        ("Рабочее место", "bi-laptop"), ("Завтрак", "bi-cup-hot"), ("Спортзал", "bi-bicycle")
    ]
    am_ids = []
    for am_name, icon in amenities:
        am_id = await con.fetchval(
            "INSERT INTO amenities (name, icon) VALUES ($1, $2) ON CONFLICT (name) DO UPDATE SET icon=$2 RETURNING id",
            am_name, icon
        )
        am_ids.append(am_id)
        
    # 5. Привязываем удобства к ресурсам
    print("Linking amenities to resources...")
    res_ids = [r['id'] for r in await con.fetch("SELECT id FROM resources")]
    for rid in res_ids:
        # Каждому объекту 3-7 случайных удобств
        chosen_ams = random.sample(am_ids, random.randint(3, 7))
        for aid in chosen_ams:
            await con.execute(
                "INSERT INTO resource_amenities (resource_id, amenity_id) VALUES ($1, $2) ON CONFLICT DO NOTHING",
                rid, aid
            )
            
    # 6. Создаем отзывы
    print("Seeding reviews...")
    review_texts = [
        "Отличное место, все очень понравилось!", "Чисто, уютно, вежливый персонал.",
        "Немного шумно по ночам, но в целом хорошо.", "Шикарный вид из окна!",
        "Цена соответствует качеству.", "Обязательно вернусь сюда снова.",
        "Все было супер, рекомендую!", "Не самый лучший вариант, но на пару ночей пойдет.",
        "Прекрасное расположение, все рядом.", "Очень уютная атмосфера."
    ]
    
    for _ in range(60):
        rid = random.choice(res_ids)
        uid = random.choice(user_ids)
        u_info = await con.fetchrow("SELECT name, surname FROM users WHERE id = $1", uid)
        
        await con.execute(
            """INSERT INTO reviews (resource_id, user_id, author_name, rating, comment, created_at)
               VALUES ($1, $2, $3, $4, $5, $6)""",
            rid, uid, f"{u_info['name']} {u_info['surname'][0]}.",
            random.randint(4, 5), random.choice(review_texts),
            datetime.now() - timedelta(days=random.randint(0, 30))
        )
        
    # 7. Создаем бронирования
    print("Seeding bookings...")
    statuses = ["PAID", "CREATED", "COMPLETED", "CANCELLED"]
    for _ in range(40):
        rid = random.choice(res_ids)
        uid = random.choice(user_ids)
        
        days_offset = random.randint(-20, 20)
        start_time = datetime.now() + timedelta(days=days_offset)
        end_time = start_time + timedelta(days=random.randint(1, 5))
        
        # Простая проверка на пересечение (не идеальная, но для сида пойдет)
        overlap = await con.fetchval(
            "SELECT 1 FROM bookings WHERE resource_id = $1 AND NOT (end_time <= $2 OR start_time >= $3) LIMIT 1",
            rid, start_time, end_time
        )
        if overlap: continue
        
        price = await con.fetchval("SELECT base_price FROM resources WHERE id = $1", rid)
        total_price = float(price or 0) * (end_time - start_time).days
        
        status = random.choice(statuses)
        if start_time < datetime.now() and status == "PAID":
            status = "COMPLETED"
            
        bid = await con.fetchval(
            """INSERT INTO bookings (user_id, resource_id, start_time, end_time, status, price, adults, children)
               VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id""",
            uid, rid, start_time, end_time, status, total_price, random.randint(1, 2), random.randint(0, 1)
        )
        
        # Гостевой профиль
        u_info = await con.fetchrow("SELECT name, email, phone FROM users WHERE id = $1", uid)
        await con.execute(
            """INSERT INTO guest_profiles (booking_id, name, email, phone, passport)
               VALUES ($1, $2, $3, $4, $5)""",
            bid, u_info['name'], u_info['email'], u_info['phone'], "4512 000000"
        )
        
    # 8. Логи
    print("Seeding audit logs...")
    actions = ["LOGIN", "VIEW_PROPERTY", "CREATE_BOOKING", "PAYMENT_SUCCESS", "SEARCH"]
    for _ in range(100):
        uid = random.choice(user_ids)
        rid = random.choice(res_ids)
        action = random.choice(actions)
        await con.execute(
            """INSERT INTO audit_logs (action, user_id, resource_id, details, created_at)
               VALUES ($1, $2, $3, $4, $5)""",
            action, uid, rid, f"Action {action} performed",
            datetime.now() - timedelta(minutes=random.randint(0, 10000))
        )

    print("Seeding completed successfully!")
    await con.close()

if __name__ == "__main__":
    asyncio.run(seed())
