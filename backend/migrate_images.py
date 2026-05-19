import asyncio
import asyncpg
import os

async def migrate():
    con = await asyncpg.connect("postgresql://postgres:1234@localhost:5432/creating_software")
    
    # 1. Добавляем колонку для фото
    await con.execute("ALTER TABLE resources ADD COLUMN IF NOT EXISTS image_url TEXT")
    
    # 2. Обновляем данные (используем локальные фото)
    updates = [
        (1, "../img/property/metro-plus.png"), 
        (2, "../img/property/lesnau-skazka.webp"),
        (3, "../img/property/komnata-arbat.jpg"),
        (4, "../img/property/kotedzh-luxery.webp"),
        (5, "../img/property/studia.jpg"),
        (6, "../img/property/dacha-u-ozera.jpg"),
    ]
    
    for rid, url in updates:
        await con.execute("UPDATE resources SET image_url = $1 WHERE id = $2", url, rid)
    
    print("Migration and data update successful!")
    await con.close()

if __name__ == "__main__":
    asyncio.run(migrate())
