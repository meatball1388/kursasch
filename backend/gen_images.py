"""
Генерирует красивые gradient-картинки для объектов недвижимости через PIL.
"""
import asyncio, asyncpg, dotenv, os, struct, zlib
dotenv.load_dotenv()

IMG_DIR = r"c:\xampp\htdocs\kursach\img\property"
os.makedirs(IMG_DIR, exist_ok=True)

OBJECTS = [
    {"id": 1, "filename": "apt_metro.jpg",    "label": "Appart Metro Plus",   "color1": (99,179,237),  "color2": (49,130,206)},
    {"id": 2, "filename": "house_forest.jpg", "label": "Dom Lesnaya Skazka", "color1": (104,211,145), "color2": (47,133,90)},
    {"id": 3, "filename": "room_arbat.jpg",   "label": "Komnata Arbat",       "color1": (246,173,85),  "color2": (221,107,32)},
    {"id": 4, "filename": "cottage_vip.jpg",  "label": "Kottedzh VIP",        "color1": (183,148,244), "color2": (128,90,213)},
    {"id": 5, "filename": "apartment_city.jpg",  "label": "Apartment City Center", "color1": (99,235,218),  "color2": (49,151,149)},
    {"id": 6, "filename": "dacha_lake.jpg",   "label": "Dacha u Ozera",       "color1": (144,205,244), "color2": (66,153,225)},
    {"id": 8, "filename": "apt_msk.jpg",      "label": "Kvartira Moskva",     "color1": (252,129,74),  "color2": (221,75,57)},
]

def generate_image(filepath, label, color1, color2):
    try:
        from PIL import Image, ImageDraw
        W, H = 800, 500
        img = Image.new('RGB', (W, H))
        draw = ImageDraw.Draw(img)
        
        # Gradient background
        for y in range(H):
            t = y / H
            r = int(color1[0] * (1-t) + color2[0] * t)
            g = int(color1[1] * (1-t) + color2[1] * t)
            b = int(color1[2] * (1-t) + color2[2] * t)
            draw.line([(0, y), (W, y)], fill=(r, g, b))
        
        # Dark overlay rectangle (simulating room/photo)
        draw.rectangle([60, 60, W-60, H-60], fill=tuple(max(0,c-60) for c in color2), outline=None)
        draw.rectangle([80, 80, W-80, H-80], fill=tuple(max(0,c-40) for c in color1))
        
        # Window shape
        for i in range(3):
            x = 110 + i * 200
            draw.rectangle([x, 100, x+120, 220], fill=(200,230,255), outline=(150,180,220), width=3)
            draw.line([(x+60, 100), (x+60, 220)], fill=(150,180,220), width=2)
            draw.line([(x, 160), (x+120, 160)], fill=(150,180,220), width=2)
        
        # Floor line
        draw.rectangle([80, 350, W-80, H-80], fill=tuple(max(0,c-80) for c in color2))
        
        # Save
        img.save(filepath, "JPEG", quality=90)
        print(f"OK: {filepath} ({os.path.getsize(filepath)//1024}KB)")
        return True
    except ImportError:
        print("PIL not found, trying fallback...")
        return False

# Check if PIL available, if not - install
try:
    from PIL import Image
    print("PIL available")
except ImportError:
    print("Installing Pillow...")
    os.system("pip install Pillow -q")

results = {}
for obj in OBJECTS:
    filepath = os.path.join(IMG_DIR, obj["filename"])
    print(f"\nGenerating id={obj['id']}: {obj['filename']}")
    ok = generate_image(filepath, obj["label"], obj["color1"], obj["color2"])
    if ok:
        results[obj["id"]] = "../img/property/" + obj["filename"]
    else:
        print(f"FAILED: {obj['filename']}")

print(f"\nGenerated {len(results)}/{len(OBJECTS)} images")

# Update DB
async def update_db():
    con = await asyncpg.connect(dsn=os.getenv("DB_URL"))
    for rid, local_path in results.items():
        await con.execute("UPDATE resources SET image_url = $1 WHERE id = $2", local_path, rid)
        print(f"DB: id={rid} -> {local_path}")
    await con.close()
    print("DB updated OK")

if results:
    asyncio.run(update_db())
