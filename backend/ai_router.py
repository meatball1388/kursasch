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
            "cottedzh": "cottedzh"
        }
        db_type = type_map.get(body.property_type, body.property_type) # Fallback to raw value if not mapped
        print(f"DEBUG AI: Mapped db_type='{db_type}'")

        async with pool.acquire() as con:
            # Строгая фильтрация по типу и цене.
            rows = await con.fetch(
                """SELECT id, type, location, base_price FROM resources 
                   WHERE is_active = TRUE AND type = $1 AND base_price >= $2 AND base_price <= $3""",
                db_type, body.min_price, body.max_price
            )

            if not rows:
                return {"status": "success", "recommendations": []}

            candidates = [dict(r) for r in rows]

            # Rank candidates using AI model
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
