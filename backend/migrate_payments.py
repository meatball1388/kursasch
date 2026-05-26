
import asyncio
import asyncpg
import os
import dotenv

async def migrate():
    dotenv.load_dotenv('backend/.env')
    db_url = os.getenv("DB_URL")
    if not db_url:
        print("DB_URL not found in .env")
        return

    try:
        conn = await asyncpg.connect(db_url)
        print("Connected to database.")
        
        # Check if external_id column exists
        check_col = await conn.fetchval("""
            SELECT count(*)
            FROM information_schema.columns 
            WHERE table_name='payments' AND column_name='external_id';
        """)
        
        if check_col == 0:
            print("Adding external_id column to payments table...")
            await conn.execute("ALTER TABLE payments ADD COLUMN external_id VARCHAR(100);")
            print("Column added successfully.")
        else:
            print("Column external_id already exists.")
            
        await conn.close()
    except Exception as e:
        print(f"Error during migration: {e}")

if __name__ == "__main__":
    asyncio.run(migrate())
