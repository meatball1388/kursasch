import asyncio
import asyncpg
import os
import json
import dotenv

dotenv.load_dotenv()

async def run():
    dsn = os.getenv("DB_URL")
    print(f"Connecting to: {dsn}")
    try:
        con = await asyncpg.connect(dsn=dsn)
        print("Connected successfully!")
        
        # 1. Выполняем SQL для создания таблиц
        print("Creating new tables...")
        with open('normalize_schema.sql', 'r', encoding='utf-8') as f:
            sql = f.read()
            await con.execute(sql)
            
        # 2. Миграция Amenities
        print("Migrating amenities...")
        # Проверяем, существует ли еще колонка amenities в resources
        col_exists = await con.fetchval("""
            SELECT count(*) FROM information_schema.columns 
            WHERE table_name='resources' AND column_name='amenities'
        """)
        
        if col_exists:
            rows = await con.fetch("SELECT id, amenities FROM resources")
            for row in rows:
                res_id = row['id']
                am_data = row['amenities']
                if not am_data:
                    continue
                
                try:
                    # Пытаемся распарсить как JSON
                    am_list = json.loads(am_data)
                    if not isinstance(am_list, list):
                        am_list = [str(am_list)]
                except:
                    # Если не JSON, считаем строкой (например, через запятую)
                    am_list = [s.strip() for s in am_data.split(',') if s.strip()]
                
                for am_name in am_list:
                    # Получаем или создаем id удобства
                    am_id = await con.fetchval(
                        "INSERT INTO amenities (name) VALUES ($1) ON CONFLICT (name) DO UPDATE SET name=EXCLUDED.name RETURNING id",
                        am_name
                    )
                    # Связываем
                    await con.execute(
                        "INSERT INTO resource_amenities (resource_id, amenity_id) VALUES ($1, $2) ON CONFLICT DO NOTHING",
                        res_id, am_id
                    )
            print(f"  Amenities migrated for {len(rows)} resources.")
            
            # Удаляем старую колонку
            print("  Dropping 'amenities' column from resources...")
            await con.execute("ALTER TABLE resources DROP COLUMN amenities")
        else:
            print("  Column 'amenities' already removed or never existed.")

        # 3. Миграция Guest Data из bookings
        print("Migrating guest profiles...")
        booking_cols = await con.fetch("""
            SELECT column_name FROM information_schema.columns 
            WHERE table_name='bookings' AND column_name IN ('name', 'email', 'phone', 'passport')
        """)
        col_names = [r['column_name'] for r in booking_cols]
        
        if col_names:
            select_cols = ", ".join(col_names)
            rows = await con.fetch(f"SELECT id, {select_cols} FROM bookings")
            for row in rows:
                await con.execute("""
                    INSERT INTO guest_profiles (booking_id, name, email, phone, passport)
                    VALUES ($1, $2, $3, $4, $5)
                    ON CONFLICT (booking_id) DO NOTHING
                """, 
                row['id'], 
                row.get('name'), 
                row.get('email'), 
                row.get('phone'), 
                row.get('passport'))
            
            print(f"  Guest profiles migrated for {len(rows)} bookings.")
            
            # Удаляем колонки
            for col in col_names:
                print(f"  Dropping '{col}' column from bookings...")
                await con.execute(f"ALTER TABLE bookings DROP COLUMN {col}")
        else:
            print("  Guest columns already removed from bookings.")

        print("Normalization completed successfully!")
        await con.close()
    except Exception as e:
        print(f"Error: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    asyncio.run(run())
