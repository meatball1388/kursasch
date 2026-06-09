import os
import asyncio
import asyncpg
from dotenv import load_dotenv

load_dotenv()

async def list_resources():
    try:
        # Try localhost first
        dsn = os.getenv("DB_URL").replace("@db:", "@localhost:")
        con = await asyncpg.connect(dsn=dsn)
        rows = await con.fetch("SELECT id, name, type, price_per_night FROM resources")
        print("ID | Name | Type | Price")
        print("-" * 50)
        for r in rows:
            print(f"{r['id']} | {r['name']} | {r['type']} | {r['price_per_night']}")
        await con.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    asyncio.run(list_resources())
