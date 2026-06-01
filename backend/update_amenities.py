import asyncio
import asyncpg
import os
import json
from dotenv import load_dotenv

async def update_amenities():
    # Загружаем URL БД из .env
    env_path = os.path.join(os.path.dirname(__file__), '.env')
    load_dotenv(env_path)
    db_url = os.getenv("DB_URL")
    if db_url:
        db_url = db_url.replace("@db:", "@localhost:")
    
    if not db_url:
        print("Ошибка: DB_URL не найден в .env")
        return

    # Данные для обновления удобств
    # wifi, parking, ac, kitchen
    updates = {
        1: ["wifi", "kitchen", "ac", "tv"],        # Metro Plus
        2: ["wifi", "parking", "kitchen", "pool"],   # Лесная сказка
        3: ["wifi", "tv"],                           # Комната на Арбате
        4: ["wifi", "parking", "kitchen", "ac", "tv", "pool", "gym", "washer"], # VIP Luxury
        5: ["wifi", "kitchen", "ac", "tv"],        # City Center
        6: ["wifi", "parking", "kitchen", "pool"]    # У озера
    }

    try:
        conn = await asyncpg.connect(db_url)
        print("Подключено к базе данных.")

        for res_id, amenities in updates.items():
            amenities_json = json.dumps(amenities)
            await conn.execute('''
                UPDATE resources 
                SET amenities = $1 
                WHERE id = $2
            ''', amenities_json, res_id)
            print(f"Объект ID {res_id} обновлен удобствами: {', '.join(amenities)}")

        await conn.close()
        print("Обновление завершено успешно.")
    except Exception as e:
        print(f"Ошибка: {e}")

if __name__ == '__main__':
    asyncio.run(update_amenities())
