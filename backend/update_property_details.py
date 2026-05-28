import asyncio
import asyncpg
import os
from dotenv import load_dotenv

async def update_data():
    # Загружаем URL БД из .env
    env_path = os.path.join(os.path.dirname(__file__), '.env')
    load_dotenv(env_path)
    db_url = os.getenv("DB_URL")
    
    if not db_url:
        print("Ошибка: DB_URL не найден в .env")
        return

    # Данные для обновления (id: {area, guests, bedrooms})
    updates = {
        1: (45, 2, 1),   # Metro Plus
        2: (120, 6, 3),  # Лесная сказка
        3: (15, 1, 1),   # Комната на Арбате
        4: (250, 8, 4),  # VIP Luxury
        5: (38, 2, 1),   # City Center
        6: (65, 4, 2),   # У озера
    }

    try:
        conn = await asyncpg.connect(db_url)
        print("Подключено к базе данных.")

        for res_id, values in updates.items():
            area, guests, bedrooms = values
            await conn.execute('''
                UPDATE resources 
                SET area = $1, guests = $2, bedrooms = $3 
                WHERE id = $4
            ''', area, guests, bedrooms, res_id)
            print(f"Объект ID {res_id} обновлен: {area}м², {guests} чел, {bedrooms} сп.")

        await conn.close()
        print("Обновление завершено.")
    except Exception as e:
        print(f"Ошибка: {e}")

if __name__ == '__main__':
    asyncio.run(update_data())
