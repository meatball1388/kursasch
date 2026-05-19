import asyncio, asyncpg, dotenv, os
dotenv.load_dotenv()

# Маппинг: id ресурса -> имя файла в img/property/
MAPPING = {
    1: '../img/property/metro-plus.png',
    2: '../img/property/lesnau-skazka.webp',
    3: '../img/property/komnata-arbat.jpg',
    4: '../img/property/kotedzh-luxery.webp',
    5: '../img/property/studia.jpg',
    6: '../img/property/dacha-u-ozera.jpg',
    8: '../img/property/metro-plus.png',  # нет своей - берём апартаменты
}

async def run():
    con = await asyncpg.connect(dsn=os.getenv('DB_URL'))
    for rid, path in MAPPING.items():
        await con.execute('UPDATE resources SET image_url = $1 WHERE id = $2', path, rid)
        print(f'id={rid} -> {path}')
    await con.close()
    print('Done')

asyncio.run(run())
