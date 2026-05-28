import asyncio
import asyncpg
import os
import random
from dotenv import load_dotenv

async def seed_reviews():
    env_path = os.path.join(os.path.dirname(__file__), '.env')
    load_dotenv(env_path)
    db_url = os.getenv("DB_URL")
    
    if not db_url:
        print("Ошибка: DB_URL не найден")
        return

    reviews_data = [
        {"name": "Алексей", "rating": 5, "comment": "Отличное место, все очень чисто и уютно. Рекомендую!"},
        {"name": "Мария", "rating": 4, "comment": "Хорошая локация, но было немного шумно от соседей. В остальном все супер."},
        {"name": "Иван", "rating": 5, "comment": "Прекрасный вид из окна и очень вежливый персонал."},
        {"name": "Елена", "rating": 5, "comment": "Все соответствует описанию. Обязательно вернусь сюда еще раз."},
        {"name": "Дмитрий", "rating": 4, "comment": "Удобная кровать, хорошая техника. Не хватило только тапочек."},
        {"name": "Анна", "rating": 3, "comment": "В целом нормально, но ремонт уже немного уставший."},
        {"name": "Сергей", "rating": 5, "comment": "Лучший вариант за эти деньги. Очень доволен."},
        {"name": "Ольга", "rating": 5, "comment": "Тихое и спокойное место, идеально для отдыха."},
    ]

    try:
        conn = await asyncpg.connect(db_url)
        print("Подключено к БД.")

        # Получаем список всех активных объектов
        resources = await conn.fetch("SELECT id FROM resources WHERE is_active = TRUE")
        
        for res in resources:
            res_id = res['id']
            # Генерируем от 3 до 6 отзывов для каждого объекта
            num_reviews = random.randint(3, 6)
            sampled_reviews = random.sample(reviews_data, num_reviews)
            
            for rev in sampled_reviews:
                await conn.execute('''
                    INSERT INTO reviews (resource_id, author_name, rating, comment)
                    VALUES ($1, $2, $3, $4)
                ''', res_id, rev['name'], rev['rating'], rev['comment'])
            
            print(f"Добавлено {num_reviews} отзывов для объекта ID {res_id}")

        await conn.close()
        print("Генерация отзывов завершена.")
    except Exception as e:
        print(f"Ошибка: {e}")

if __name__ == '__main__':
    asyncio.run(seed_reviews())
