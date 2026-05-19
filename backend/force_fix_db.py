import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def fix():
    dsn = os.getenv("DB_URL")
    print(f"Connecting to: {dsn}")
    try:
        con = await asyncpg.connect(dsn=dsn)
        print("Connected successfully!")
        
        # Check current state
        rows = await con.fetch("SELECT id, image_url FROM resources")
        print("Current URLs in DB:")
        for r in rows:
            print(f"  {r['id']}: {r['image_url']}")
            
        # FORCE UPDATE
        await con.execute("""
            UPDATE resources SET image_url = 
            CASE 
                WHEN id = 1 THEN '../img/property/metro-plus.png'
                WHEN id = 2 THEN '../img/property/lesnau-skazka.webp'
                WHEN id = 3 THEN '../img/property/komnata-arbat.jpg'
                WHEN id = 4 THEN '../img/property/kotedzh-luxery.webp'
                WHEN id = 5 THEN '../img/property/studia.jpg'
                WHEN id = 6 THEN '../img/property/dacha-u-ozera.jpg'
                ELSE '../img/property/metro-plus.png'
            END
        """)
        print("Update executed.")
        
        # Verify
        rows = await con.fetch("SELECT id, image_url FROM resources")
        print("New URLs in DB:")
        for r in rows:
            print(f"  {r['id']}: {r['image_url']}")
            
        await con.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    asyncio.run(fix())
