from fastapi import APIRouter, HTTPException, Request
from pydantic import BaseModel
from typing import Optional
from ai_recommender import train_model, get_recommendations, load_model

ai_router = APIRouter()

class RecommendRequest(BaseModel):
    city: str
    property_type: str
    min_price: float
    max_price: float
    rooms: int
    amenities: list[str] = []
    check_in: str
    check_out: str
    guests: int
    top_n: Optional[int] = 5

@ai_router.post("/train")
async def train():
    try:
        metrics = train_model()
        return {
            "status": "success",
            "message": "Модель обучена и сохранена",
            "metrics": metrics
        }
    except FileNotFoundError as e:
        raise HTTPException(status_code=404, detail=f"Файл данных не найден: {e}")
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Ошибка обучения: {e}")

@ai_router.post("/recommend")
async def recommend(request: Request, body: RecommendRequest):
    pool = request.app.state.pool
    try:
        print(f"DEBUG AI: Requested property_type='{body.property_type}'")
        # Mapping frontend types to DB types
        type_map = {
            "apartment": "apartment",
            "house": "dacha",
            "villa": "cottedzh",
            "room": "room",
            "dacha": "dacha",
            "cottedzh": "cottedzh",
            "any": "any"
        }
        db_type = type_map.get(body.property_type, body.property_type)
        print(f"DEBUG AI: Mapped db_type='{db_type}', city='{body.city}'")

        async with pool.acquire() as con:
            # Строгая фильтрация по цене и городу
            conditions = ["is_active = TRUE", "price_per_night >= $1", "price_per_night <= $2"]
            params = [body.min_price, body.max_price]

            if db_type != "any":
                conditions.append(f"rt.name = ${len(params) + 1}")
                params.append(db_type)

            if body.city and body.city != "any":
                # Специальная обработка для Москвы, чтобы включать Московскую область
                if "Москва" in body.city:
                    conditions.append(f"(c.name ILIKE ${len(params) + 1} OR c.name ILIKE 'Московская%')")
                    params.append(f"%{body.city}%")
                else:
                    conditions.append(f"c.name ILIKE ${len(params) + 1}")
                    params.append(f"%{body.city}%")

            query = f"""SELECT r.id, r.name, rt.name as type, c.name as location, r.price_per_night, r.area, r.guests, r.bedrooms, 
                               COALESCE((SELECT array_agg(a.name) FROM resource_amenities ra JOIN amenities a ON ra.amenity_id = a.id WHERE ra.resource_id = r.id), '{{}}') as amenities
                        FROM resources r
                        LEFT JOIN cities c ON r.city_id = c.id
                        LEFT JOIN resource_types rt ON r.type_id = rt.id
                        WHERE {" AND ".join(conditions)}"""

            rows = await con.fetch(query, *params)

            if not rows:
                return {"status": "success", "recommendations": []}
            candidates = [dict(r) for r in rows]

            # Rank candidates using AI model
            # Мы передаем candidates в get_recommendations, где они будут обработаны
            results = get_recommendations(
                city=body.city,
                property_type=body.property_type,
                min_price=body.min_price,
                max_price=body.max_price,
                rooms=body.rooms,
                amenities=body.amenities,
                check_in=body.check_in,
                check_out=body.check_out,
                guests=body.guests,
                candidates=candidates,
                top_n=body.top_n,
            )
        return {"status": "success", "recommendations": results}
    except FileNotFoundError as e:
        raise HTTPException(status_code=503, detail=str(e))
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Ошибка рекомендации: {e}")

@ai_router.get("/status")
async def status():
    try:
        load_model()
        return {"status": "ready", "message": "Модель загружена и готова к работе"}
    except FileNotFoundError:
        return {"status": "not_trained", "message": "Модель не обучена. Вызовите POST /ai/train"}
