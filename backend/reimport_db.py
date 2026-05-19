import asyncio
import asyncpg
import os

with open("create_tables.sql", "r", encoding="utf-8") as f:
    sql = f.read()

async def reset_db():
    con = await asyncpg.connect("postgresql://postgres:1234@localhost:5432/creating_software")
    # Удаляем старые таблицы перед импортом
    await con.execute("DROP TABLE IF EXISTS bookings CASCADE;")
    await con.execute("DROP TABLE IF EXISTS resources CASCADE;")
    await con.execute("DROP TABLE IF EXISTS users CASCADE;")
    
    await con.execute(sql)
    print("DB reset successful!")
    await con.close()

if __name__ == "__main__":
    asyncio.run(reset_db())
