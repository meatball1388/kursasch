import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def test_conn():
    dsn = os.getenv("DB_URL")
    print(f"Connecting to {dsn}...")
    try:
        con = await asyncpg.connect(dsn=dsn)
        print("Success!")
        await con.close()
    except Exception as e:
        print(f"Failed: {e}")

if __name__ == "__main__":
    asyncio.run(test_conn())
