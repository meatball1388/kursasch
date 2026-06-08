import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def reimport():
    db_url = os.getenv("DB_URL")
    if not db_url:
        print("ОШИБКА: DB_URL не найден в .env")
        return
        
    con = await asyncpg.connect(db_url)
    print("Re-importing schema from init_db.sql...")
    
    with open('init_db.sql', 'r', encoding='utf-8') as f:
        sql = f.read()
        
    await con.execute(sql)
    print("Success!")
    await con.close()

if __name__ == "__main__":
    asyncio.run(reimport())
