import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def check():
    con = await asyncpg.connect(dsn=os.getenv("DB_URL"))
    rows = await con.fetch("SELECT DISTINCT type FROM resources")
    for r in rows:
        print(f"Type: {r['type']}")
    await con.close()

if __name__ == "__main__":
    asyncio.run(check())
