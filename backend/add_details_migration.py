import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def migrate():
    dsn = os.getenv("DB_URL")
    print(f"Connecting to: {dsn}")
    try:
        con = await asyncpg.connect(dsn=dsn)
        print("Connected successfully!")
        
        # Add columns if they don't exist
        print("Adding columns area, guests, bedrooms...")
        await con.execute("""
            ALTER TABLE resources ADD COLUMN IF NOT EXISTS area INTEGER DEFAULT 0;
            ALTER TABLE resources ADD COLUMN IF NOT EXISTS guests INTEGER DEFAULT 0;
            ALTER TABLE resources ADD COLUMN IF NOT EXISTS bedrooms INTEGER DEFAULT 0;
        """)
        
        # Update existing data
        print("Updating existing resources with details...")
        updates = [
            (1, 45, 2, 1),   # Metro Plus
            (2, 120, 6, 3),  # Lesnaya Skazka
            (3, 15, 1, 1),   # Komnata
            (4, 250, 8, 4),  # VIP Luxury
            (5, 38, 2, 1),   # City Center
            (6, 65, 4, 2),   # U Ozera
        ]
        
        for res_id, area, guests, bedrooms in updates:
            await con.execute("""
                UPDATE resources 
                SET area = $2, guests = $3, bedrooms = $4 
                WHERE id = $1
            """, res_id, area, guests, bedrooms)
            print(f"  Resource {res_id} updated: {area}m2, {guests} guests, {bedrooms} bedrooms")
            
        await con.close()
        print("Migration completed successfully!")
    except Exception as e:
        print(f"Error during migration: {e}")

if __name__ == "__main__":
    asyncio.run(migrate())
