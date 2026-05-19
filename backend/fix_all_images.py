import asyncio
import asyncpg
import os
import dotenv

dotenv.load_dotenv()

MAPPING = {
    'metro-plus': '../img/property/metro-plus.png',
    'lesnau-skazka': '../img/property/lesnau-skazka.webp',
    'komnata-arbat': '../img/property/komnata-arbat.jpg',
    'kotedzh-luxery': '../img/property/kotedzh-luxery.webp',
    'studia': '../img/property/studia.jpg',
    'dacha-u-ozera': '../img/property/dacha-u-ozera.jpg'
}

# Mapping based on IDs for certainty
ID_MAPPING = {
    1: '../img/property/metro-plus.png',
    2: '../img/property/lesnau-skazka.webp',
    3: '../img/property/komnata-arbat.jpg',
    4: '../img/property/kotedzh-luxery.webp',
    5: '../img/property/studia.jpg',
    6: '../img/property/dacha-u-ozera.jpg',
    8: '../img/property/metro-plus.png'
}

async def fix():
    dsn = os.getenv("DB_URL")
    print(f"Connecting to {dsn}...")
    con = await asyncpg.connect(dsn=dsn)
    
    # Update known IDs
    for rid, path in ID_MAPPING.items():
        await con.execute("UPDATE resources SET image_url = $1 WHERE id = $2", path, rid)
        print(f"Updated ID {rid} -> {path}")
        
    # Also update any remaining http links to a default local image
    await con.execute("UPDATE resources SET image_url = '../img/property/metro-plus.png' WHERE image_url LIKE 'http%'")
    print("All HTTP links converted to local default.")
    
    await con.close()
    print("Done!")

if __name__ == "__main__":
    asyncio.run(fix())
