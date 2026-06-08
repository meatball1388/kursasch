import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def analyze():
    dsn = os.getenv("DB_URL")
    con = await asyncpg.connect(dsn=dsn)
    
    locs = await con.fetch("SELECT DISTINCT location FROM resources")
    types = await con.fetch("SELECT DISTINCT type FROM resources")
    reviews_check = await con.fetch("SELECT author_name, user_id FROM reviews LIMIT 10")
    
    print("LOCS:", [r['location'] for r in locs])
    print("TYPES:", [r['type'] for r in types])
    print("REVIEWS (author_name, user_id):", [(r['author_name'], r['user_id']) for r in reviews_check])
    
    await con.close()

if __name__ == "__main__":
    asyncio.run(analyze())
