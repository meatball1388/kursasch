import os
import json
import pickle
import numpy as np
import pandas as pd
from pathlib import Path
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import accuracy_score, precision_score, recall_score

MODEL_PATH = Path("model.pkl")
ENCODERS_PATH = Path("encoders.pkl")
DATA_PATH = Path("training_data.csv")

CITIES = ["Москва", "Санкт-Петербург", "Казань", "Сочи", "Екатеринбург", "Новосибирск", "Краснодар", "Нижний Новгород"]
PROPERTY_TYPES = ["apartment", "house", "room", "villa"]
AMENITIES_POOL = ["wifi", "parking", "kitchen", "pool", "gym", "ac", "balcony", "washer", "tv", "pets"]


def _build_features(df: pd.DataFrame, city_enc: LabelEncoder, type_enc: LabelEncoder) -> np.ndarray:
    city_col = city_enc.transform(df["city"].fillna("Москва"))
    type_col = type_enc.transform(df["property_type"].fillna("apartment"))
    price_range = (df["max_price"] - df["min_price"]).fillna(0).values
    rooms = df["rooms"].fillna(0).values
    guests = df["guests"].fillna(1).values
    nights = (
        pd.to_datetime(df["check_out"], errors="coerce") -
        pd.to_datetime(df["check_in"], errors="coerce")
    ).dt.days.fillna(1).values

    amenity_matrix = np.zeros((len(df), len(AMENITIES_POOL)))
    for i, row in enumerate(df["amenities"]):
        try:
            items = json.loads(row) if isinstance(row, str) else (row or [])
        except Exception:
            items = []
        for item in items:
            if item in AMENITIES_POOL:
                amenity_matrix[i, AMENITIES_POOL.index(item)] = 1

    return np.column_stack([city_col, type_col, price_range, rooms, guests, nights, amenity_matrix])


def train_model(data_path: str = str(DATA_PATH)) -> dict:
    df = pd.read_csv(data_path)

    city_enc = LabelEncoder().fit(CITIES)
    type_enc = LabelEncoder().fit(PROPERTY_TYPES)

    df["city"] = df["city"].where(df["city"].isin(CITIES), CITIES[0])
    df["property_type"] = df["property_type"].where(df["property_type"].isin(PROPERTY_TYPES), PROPERTY_TYPES[0])

    X = _build_features(df, city_enc, type_enc)
    y = df["booked"].values

    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

    clf = RandomForestClassifier(n_estimators=100, random_state=42, n_jobs=-1)
    clf.fit(X_train, y_train)

    y_pred = clf.predict(X_test)
    metrics = {
        "accuracy": round(accuracy_score(y_test, y_pred), 4),
        "precision": round(precision_score(y_test, y_pred, zero_division=0), 4),
        "recall": round(recall_score(y_test, y_pred, zero_division=0), 4),
    }
#22
    with open(MODEL_PATH, "wb") as f:
        pickle.dump(clf, f)
    with open(ENCODERS_PATH, "wb") as f:
        pickle.dump({"city": city_enc, "type": type_enc}, f)

    return metrics


def load_model():
    if not MODEL_PATH.exists() or not ENCODERS_PATH.exists():
        raise FileNotFoundError("Модель не найдена. Запустите /ai/train сначала.")
    with open(MODEL_PATH, "rb") as f:
        clf = pickle.load(f)
    with open(ENCODERS_PATH, "rb") as f:
        encoders = pickle.load(f)
    return clf, encoders


def get_recommendations(
    city: str,
    property_type: str,
    min_price: float,
    max_price: float,
    rooms: int,
    amenities: list[str],
    check_in: str,
    check_out: str,
    guests: int,
    candidate_ids: list[int] = None,
    candidates: list[dict] = None,
    top_n: int = 5,
) -> list[dict]:
    try:
        clf, encoders = load_model()
        city_enc: LabelEncoder = encoders["city"]
        type_enc: LabelEncoder = encoders["type"]
    except Exception:
        # Fallback if no model: return simple match scores
        if candidates:
            return [{"property_id": c["id"], "score": 0.5} for c in candidates[:top_n]]
        if not candidate_ids:
            return []
        return [{"property_id": pid, "score": 0.5} for pid in candidate_ids[:top_n]]

    # If no candidates provided, we can't recommend actual items
    if not candidates and not candidate_ids:
        return []
    
    # If only candidate_ids provided, convert to simple dicts
    if not candidates:
        candidates = [{"id": pid} for pid in candidate_ids]

    results = []
    
    # DB type to Model type mapping
    db_to_model_type = {
        "dacha": "house",
        "cottedzh": "villa",
        "apartment": "apartment",
        "room": "room"
    }

    for cand in candidates:
        prop_id = cand["id"]
        
        # Use candidate features if available, otherwise use query features
        cand_type = db_to_model_type.get(cand.get("type"), property_type)
        cand_city = cand.get("location", city)
        
        # Ensure they are within CITIES and PROPERTY_TYPES for encoder
        cand_city = cand_city if cand_city in CITIES else CITIES[0]
        cand_type = cand_type if cand_type in PROPERTY_TYPES else PROPERTY_TYPES[0]

        # Use candidate's actual data for ranking
        cand_price = float(cand.get("base_price", min_price))
        cand_rooms = cand.get("bedrooms", rooms)
        cand_guests = cand.get("guests", guests)
        cand_amenities = cand.get("amenities", amenities)
        
        if isinstance(cand_amenities, str):
            try:
                cand_amenities = json.loads(cand_amenities)
            except:
                cand_amenities = []

        row = {
            "city": cand_city,
            "property_type": cand_type,
            "min_price": cand_price,
            "max_price": cand_price,
            "rooms": cand_rooms,
            "guests": cand_guests,
            "amenities": json.dumps(cand_amenities, ensure_ascii=False),
            "check_in": check_in,
            "check_out": check_out,
        }
        df_row = pd.DataFrame([row])
        X = _build_features(df_row, city_enc, type_enc)
        
        prob = clf.predict_proba(X)[0][1]
        
        # Add a small score boost if types match perfectly
        type_match_bonus = 0.15 if cand_type == property_type else 0.0
        
        # Price proximity bonus: better score if closer to user's desired budget
        price_diff_min = abs(cand_price - min_price)
        price_diff_max = abs(cand_price - max_price)
        avg_diff_pct = (price_diff_min + price_diff_max) / (max_price - min_price + 1) / 2
        price_bonus = max(0, 0.2 * (1 - avg_diff_pct))

        # Base score from model prob + bonuses
        # We also rescale prob slightly to be more 'positive' (e.g. 0.1 -> 0.5)
        raw_score = (float(prob) * 2) + type_match_bonus + price_bonus
        
        # Final scaling to 0.7 - 0.99 range for display if it's a valid candidate
        scaled_score = 0.7 + (min(raw_score, 1.0) * 0.29)
        
        results.append({"property_id": prop_id, "score": round(scaled_score, 4)})

    results.sort(key=lambda x: x["score"], reverse=True)
    return results[:top_n]
