import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def check():
    con = await asyncpg.connect(dsn=os.getenv("DB_URL"))
    rows = await con.fetch("SELECT id, name, image_url FROM resources ORDER BY id")
    print("RESOURCES IN DB:")
    for r in rows:
        print(f"  ID {r['id']}: {r['name']} ({r['image_url']})")
        
    rows = await con.fetch("SELECT id, resource_id, author_name FROM reviews")
    print(f"\nTOTAL REVIEWS: {len(rows)}")
    
    await con.close()

if __name__ == "__main__":
    asyncio.run(check())
