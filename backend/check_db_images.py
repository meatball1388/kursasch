import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def check():
    con = await asyncpg.connect(dsn=os.getenv("DB_URL"))
    rows = await con.fetch("SELECT id, image_url FROM resources")
    for row in rows:
        print(f"ID {row['id']}: {row['image_url']}")
    await con.close()

if __name__ == "__main__":
    asyncio.run(check())
