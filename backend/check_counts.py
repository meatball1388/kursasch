import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def check():
    con = await asyncpg.connect(dsn=os.getenv("DB_URL"))
    rows = await con.fetch("""
        SELECT r.id, r.name, COUNT(rv.id) as count
        FROM resources r
        LEFT JOIN reviews rv ON r.id = rv.resource_id
        GROUP BY r.id, r.name
        ORDER BY r.id
    """)
    for r in rows:
        print(f"ID {r['id']} ({r['name']}): {r['count']} reviews")
    await con.close()

if __name__ == "__main__":
    asyncio.run(check())
