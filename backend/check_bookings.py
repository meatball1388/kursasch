import os, asyncio, asyncpg, dotenv
dotenv.load_dotenv('backend/.env')

async def q():
    conn = await asyncpg.connect(os.getenv('DB_URL'))
    rows = await conn.fetch("SELECT id, resource_id, start_time, end_time, status FROM bookings")
    print("--- BOOKINGS ---")
    for r in rows:
        print(r)
    await conn.close()

if __name__ == "__main__":
    asyncio.run(q())
