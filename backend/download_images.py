"""
Скачивает картинки для объектов недвижимости с нескольких источников.
Если источник недоступен — генерирует placeholder через PIL.
"""
import asyncio, asyncpg, dotenv, os, urllib.request, shutil

dotenv.load_dotenv()

IMG_DIR = r"C:\xampp\htdocs\kursasch\front\img\property"
os.makedirs(IMG_DIR, exist_ok=True)

# Несколько источников для каждого объекта (пробуем по очереди)
SOURCES = [
    # Объект 1 — квартира/апартаменты
    {"id": 1, "filename": "apt_metro.jpg", "urls": [
        "https://loremflickr.com/800/500/apartment,interior",
        "https://placeimg.com/800/500/arch",
        "https://placekitten.com/800/500",
    ]},
    # Объект 2 — загородный дом
    {"id": 2, "filename": "house_forest.jpg", "urls": [
        "https://loremflickr.com/800/500/house,forest",
        "https://placeimg.com/800/500/nature",
    ]},
    # Объект 3 — комната
    {"id": 3, "filename": "room_arbat.jpg", "urls": [
        "https://loremflickr.com/800/500/room,bedroom",
        "https://placeimg.com/800/500/arch",
    ]},
    # Объект 4 — коттедж
    {"id": 4, "filename": "cottage_vip.jpg", "urls": [
        "https://loremflickr.com/800/500/villa,luxury",
        "https://placeimg.com/800/500/arch",
    ]},
    # Объект 5 — студия
    {"id": 5, "filename": "studio_city.jpg", "urls": [
        "https://loremflickr.com/800/500/studio,apartment",
        "https://placeimg.com/800/500/arch",
    ]},
    # Объект 6 — дача у озера
    {"id": 6, "filename": "dacha_lake.jpg", "urls": [
        "https://loremflickr.com/800/500/lake,house",
        "https://placeimg.com/800/500/nature",
    ]},
    # Объект 8
    {"id": 8, "filename": "apt_msk.jpg", "urls": [
        "https://loremflickr.com/800/500/apartment",
        "https://placeimg.com/800/500/arch",
    ]},
]

def try_download(filepath, urls):
    """Пробует скачать из списка URL, возвращает True при успехе."""
    headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"}
    for url in urls:
        try:
            req = urllib.request.Request(url, headers=headers)
            with urllib.request.urlopen(req, timeout=10) as resp, open(filepath, 'wb') as f:
                shutil.copyfileobj(resp, f)
            size = os.path.getsize(filepath)
            if size > 1000:  # минимум 1KB — не пустой файл
                print(f"  ✅ Скачано с {url} ({size//1024} KB)")
                return True
            else:
                print(f"  ⚠ Слишком маленький файл ({size} B), пробуем следующий...")
        except Exception as e:
            print(f"  ❌ {url}: {e}")
    return False

def generate_placeholder(filepath, label, color):
    """Генерирует цветной placeholder без PIL."""
    # Создаём минимальный JPG через встроенные средства
    # Используем SVG → нет, нужен бинарный JPG
    # Попробуем через PIL/Pillow если есть
    try:
        from PIL import Image, ImageDraw, ImageFont
        img = Image.new('RGB', (800, 500), color=color)
        draw = ImageDraw.Draw(img)
        draw.rectangle([0, 0, 800, 500], fill=color)
        # Тёмный прямоугольник в центре
        draw.rectangle([150, 150, 650, 350], fill=tuple(max(0,c-40) for c in color))
        # Текст
        try:
            font = ImageFont.truetype("arial.ttf", 36)
        except:
            font = ImageFont.load_default()
        draw.text((400, 250), label, fill=(255,255,255), font=font, anchor="mm")
        img.save(filepath, "JPEG", quality=85)
        print(f"  🎨 Сгенерирован placeholder: {label}")
        return True
    except ImportError:
        print("  PIL недоступен, создаём заглушку...")
        # Просто копируем существующую картинку если есть
        existing = os.path.join(IMG_DIR, "room_example.png")
        if os.path.exists(existing):
            shutil.copy(existing, filepath)
            return True
        return False

COLORS = {
    1: (74, 144, 226),   # синий
    2: (80, 160, 80),    # зелёный
    3: (180, 120, 60),   # коричневый
    4: (150, 60, 150),   # фиолетовый
    5: (60, 180, 180),   # бирюзовый
    6: (60, 120, 200),   # голубой
    8: (200, 100, 60),   # оранжевый
}

LABELS = {
    1: "Апартаменты Metro Plus", 2: "Дом Лесная сказка",
    3: "Комната Арбат", 4: "Коттедж VIP Luxury",
    5: "Студия City Center", 6: "Дача У озера",
    8: "Квартира Москва",
}

results = {}
for src in SOURCES:
    rid = src["id"]
    filepath = os.path.join(IMG_DIR, src["filename"])
    print(f"\nОбъект ID={rid}: {src['filename']}")
    
    if not try_download(filepath, src["urls"]):
        print(f"  Все URL недоступны, генерируем placeholder...")
        generate_placeholder(filepath, LABELS.get(rid, f"Объект {rid}"), COLORS.get(rid, (100,100,100)))
    
    results[rid] = "img/property/" + src["filename"]

# Обновляем БД
async def update_db():
    con = await asyncpg.connect(dsn=os.getenv("DB_URL"))
    for rid, local_path in results.items():
        await con.execute("UPDATE resources SET image_url = $1 WHERE id = $2", local_path, rid)
        print(f"DB updated: id={rid} -> {local_path}")
    await con.close()
    print("\n✅ БД обновлена — используем локальные картинки")

asyncio.run(update_db())
