import asyncio
import asyncpg
import os
import dotenv

# Загружаем .env
env_path = os.path.join(os.path.dirname(__file__), '.env')
if os.path.exists(env_path):
    dotenv.load_dotenv(env_path)
else:
    dotenv.load_dotenv()

async def test_db():
    print("--- ТЕСТ ПОДКЛЮЧЕНИЯ К БД ---")
    db_url = os.getenv("DB_URL")
    if not db_url:
        print("ОШИБКА: DB_URL не найден в .env")
        return

    try:
        conn = await asyncpg.connect(db_url)
        print("УСПЕХ: Подключение к PostgreSQL установлено.")
        
        # Проверяем количество ресурсов
        count = await conn.fetchval("SELECT COUNT(*) FROM resources WHERE is_active = TRUE")
        print(f"ИНФО: Найдено активных товаров в базе: {count}")
        
        if count > 0:
            # Проверяем пути к картинкам и типы
            rows = await conn.fetch("SELECT id, name, type, location, address, is_active FROM resources LIMIT 20")
            print("\n--- ПОЛНЫЙ СПИСОК ОБЪЕКТОВ ---")
            for row in rows:
                print(f"ID: {row['id']} | {row['name']} | Тип: {row['type']} | Город: {row['location']} | Активен: {row['is_active']}")
            
            # Проверка конкретно Московской области
            mo_count = await conn.fetchval("SELECT COUNT(*) FROM resources WHERE location ILIKE '%Московская область%'")
            print(f"\nИНФО: Найдено в Московской области: {mo_count}")
        
        await conn.close()
    except Exception as e:
        print(f"ОШИБКА БД: {e}")

if __name__ == "__main__":
    asyncio.run(test_db())
