import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def update_reviews():
    dsn = os.getenv("DB_URL")
    print(f"Connecting to {dsn}...")
    try:
        con = await asyncpg.connect(dsn=dsn)
        
        # Read SQL file
        sql_path = os.path.join(os.path.dirname(__file__), "add_reviews.sql")
        with open(sql_path, "r", encoding="utf-8") as f:
            sql = f.read()
            
        # Execute SQL
        await con.execute(sql)
        print("Successfully added more reviews to the database!")
        
        await con.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    asyncio.run(update_reviews())
