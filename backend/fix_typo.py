import os, asyncio, asyncpg, dotenv
dotenv.load_dotenv('backend/.env')

async def q():
    conn = await asyncpg.connect(os.getenv('DB_URL'))
    await conn.execute("UPDATE resources SET type = 'apartment' WHERE type = 'appartment'")
    await conn.close()
    print("DB updated: appartment -> apartment")

if __name__ == "__main__":
    asyncio.run(q())
