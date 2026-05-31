import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()
DB_URL = os.getenv("DB_URL")
# ВАЖНО: Внутри докера хост 'db' доступен, снаружи - нет (используем localhost)
if DB_URL and not os.path.exists('/.dockerenv'):
    DB_URL = DB_URL.replace("@db:", "@localhost:")

async def check():
    print(f"Connecting to {DB_URL}...")
    conn = await asyncpg.connect(DB_URL)
    try:
        tables = ['users', 'resources', 'bookings', 'payments', 'favorites']
        for t in tables:
            try:
                count = await conn.fetchval(f"SELECT COUNT(*) FROM {t}")
                print(f"Table {t} | Rows: {count}")
                if count > 0:
                    order_col = 'created_at' if t != 'users' else 'id'
                    last = await conn.fetchrow(f"SELECT * FROM {t} ORDER BY {order_col} DESC LIMIT 1")
                    print(f"  Last: {dict(last)}")
            except Exception as e:
                print(f"Table {t} | Error: {e}")
    finally:
        await conn.close()

if __name__ == "__main__":
    asyncio.run(check())
