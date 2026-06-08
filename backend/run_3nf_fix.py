import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def fix_3nf():
    dsn = os.getenv("DB_URL")
    con = await asyncpg.connect(dsn=dsn)
    
    print("Starting 3NF Normalization...")

    # 1. Справочник городов (Cities)
    print("Creating 'cities' table...")
    await con.execute("""
        CREATE TABLE IF NOT EXISTS cities (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) UNIQUE NOT NULL
        )
    """)
    
    # 2. Справочник типов (Resource Types)
    print("Creating 'resource_types' table...")
    await con.execute("""
        CREATE TABLE IF NOT EXISTS resource_types (
            id SERIAL PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            display_name VARCHAR(100)
        )
    """)
    
    # Заполняем типы (для 3НФ важно иметь отдельную таблицу для атрибутов типа, если они появятся)
    types_map = {
        'apartment': 'Апартаменты',
        'house': 'Дом',
        'room': 'Комната',
        'hotel': 'Отель',
        'cottedzh': 'Коттедж',
        'dacha': 'Дача'
    }
    for t_name, d_name in types_map.items():
        await con.execute(
            "INSERT INTO resource_types (name, display_name) VALUES ($1, $2) ON CONFLICT (name) DO NOTHING",
            t_name, d_name
        )

    # 3. Миграция Resources
    print("Normalizing 'resources' table...")
    
    # Добавляем колонки для FK
    await con.execute("ALTER TABLE resources ADD COLUMN IF NOT EXISTS city_id INTEGER REFERENCES cities(id)")
    await con.execute("ALTER TABLE resources ADD COLUMN IF NOT EXISTS type_id INTEGER REFERENCES resource_types(id)")
    
    # Заполняем города и обновляем FK
    rows = await con.fetch("SELECT DISTINCT location FROM resources WHERE location IS NOT NULL")
    for row in rows:
        loc = row['location']
        city_id = await con.fetchval(
            "INSERT INTO cities (name) VALUES ($1) ON CONFLICT (name) DO UPDATE SET name=EXCLUDED.name RETURNING id",
            loc
        )
        await con.execute("UPDATE resources SET city_id = $1 WHERE location = $2", city_id, loc)
        
    # Обновляем типы FK
    rows = await con.fetch("SELECT DISTINCT type FROM resources")
    for row in rows:
        t_name = row['type']
        type_id = await con.fetchval("SELECT id FROM resource_types WHERE name = $1", t_name)
        if type_id:
            await con.execute("UPDATE resources SET type_id = $1 WHERE type = $2", type_id, t_name)

    # Теперь можно удалить старые текстовые колонки (но для совместимости с кодом пока оставим или переименуем)
    # В 3НФ они НЕ должны быть в основной таблице.
    # await con.execute("ALTER TABLE resources DROP COLUMN location")
    # await con.execute("ALTER TABLE resources DROP COLUMN type")

    # 4. Нормализация Reviews
    print("Normalizing 'reviews' table...")
    await con.execute("ALTER TABLE reviews ALTER COLUMN author_name DROP NOT NULL")
    await con.execute("UPDATE reviews SET author_name = NULL WHERE user_id IS NOT NULL")

    print("3NF Normalization completed successfully!")
    await con.close()

if __name__ == "__main__":
    asyncio.run(fix_3nf())
