import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

async def migrate():
    dsn = os.getenv("DB_URL")
    print(f"Connecting to: {dsn}")
    con = await asyncpg.connect(dsn=dsn)
    
    async with con.transaction():
        print("1. Renaming base_price to price_per_night in resources...")
        # Check if base_price exists
        has_base_price = await con.fetchval("""
            SELECT count(*) FROM information_schema.columns 
            WHERE table_name='resources' AND column_name='base_price'
        """)
        if has_base_price:
            await con.execute("ALTER TABLE resources RENAME COLUMN base_price TO price_per_night")
            print("   Done.")
        else:
            print("   Column 'base_price' not found (maybe already renamed).")

        print("2. Adding missing created_at columns...")
        tables_with_created_at = [
            'users', 'bookings', 'guest_profiles', 'payments', 
            'reviews', 'favorites', 'audit_logs'
        ]
        for table in tables_with_created_at:
            has_col = await con.fetchval(f"""
                SELECT count(*) FROM information_schema.columns 
                WHERE table_name='{table}' AND column_name='created_at'
            """)
            if not has_col:
                print(f"   Adding created_at to {table}...")
                await con.execute(f"ALTER TABLE {table} ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
            else:
                print(f"   Table {table} already has created_at.")

        print("3. Enforcing Foreign Keys and strict schema from DBML...")
        
        # Ensure cities and resource_types tables exist (they should from previous steps, but let's be sure)
        await con.execute("""
            CREATE TABLE IF NOT EXISTS cities (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) UNIQUE NOT NULL
            );
            CREATE TABLE IF NOT EXISTS resource_types (
                id SERIAL PRIMARY KEY,
                name VARCHAR(50) UNIQUE NOT NULL,
                display_name VARCHAR(100)
            );
        """)

        # Fix resources table constraints
        print("   Updating resources constraints...")
        # Add city_id and type_id if missing
        await con.execute("ALTER TABLE resources ADD COLUMN IF NOT EXISTS city_id INTEGER REFERENCES cities(id)")
        await con.execute("ALTER TABLE resources ADD COLUMN IF NOT EXISTS type_id INTEGER REFERENCES resource_types(id)")
        
        # Add price_per_night if it somehow doesn't exist (should be renamed)
        await con.execute("ALTER TABLE resources ADD COLUMN IF NOT EXISTS price_per_night DECIMAL")

        # Drop old columns IF they are empty or redundant (location, type)
        # We'll leave them for now but they won't be used by the code. 
        # Actually, let's keep them until we are sure the code is updated.

        # Fix reviews table
        print("   Updating reviews table...")
        await con.execute("ALTER TABLE reviews ADD COLUMN IF NOT EXISTS user_id INTEGER REFERENCES users(id) ON DELETE SET NULL")
        await con.execute("ALTER TABLE reviews ALTER COLUMN author_name DROP NOT NULL")

        # Fix guest_profiles
        print("   Updating guest_profiles...")
        await con.execute("ALTER TABLE guest_profiles ADD COLUMN IF NOT EXISTS email VARCHAR(255)")

        print("4. Migration to v3 completed successfully!")

    await con.close()

if __name__ == "__main__":
    asyncio.run(migrate())
