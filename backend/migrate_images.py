import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def migrate_images():
    db_url = os.getenv("DB_URL")
    if not db_url:
        print("ОШИБКА: DB_URL не найден в .env")
        return
        
    con = await asyncpg.connect(db_url)
    # Маппинг имен файлов
    mapping = {
        1: "metro-plus.png",
        2: "lesnau-skazka.webp",
        3: "komnata-arbat.jpg",
        4: "kotedzh-luxery.webp",
        5: "studia.jpg",
        6: "dacha-u-ozera.jpg"
    }
    
    for rid, fname in mapping.items():
        url = f"../img/property/{fname}"
        await con.execute("UPDATE resources SET image_url = $1 WHERE id = $2", url, rid)
        print(f"Updated resource {rid} -> {url}")
        
    await con.close()

if __name__ == "__main__":
    asyncio.run(migrate_images())
