import asyncio, asyncpg, dotenv, os
dotenv.load_dotenv()

images = {
    1: '../img/property/metro-plus.png',
    2: '../img/property/lesnau-skazka.webp',
    3: '../img/property/komnata-arbat.jpg',
    4: '../img/property/kotedzh-luxery.webp',
    5: '../img/property/studia.jpg',
    6: '../img/property/dacha-u-ozera.jpg',
    8: '../img/property/metro-plus.png', # Default for new one
}

async def run():
    con = await asyncpg.connect(dsn=os.getenv('DB_URL'))
    for rid, url in images.items():
        await con.execute('UPDATE resources SET image_url = $1 WHERE id = $2', url, rid)
        print(f'Updated id={rid} -> {url}')
    await con.close()
    print('All done')

asyncio.run(run())
