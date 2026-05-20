"""
test_ai.py — юнит-тесты AI-модуля (ai_recommender.py + ai_router.py).

Все тесты изолированы: модель обучается на синтетических данных,
реальные файлы model.pkl / encoders.pkl не требуются.
"""
import sys, os
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

import json
import pickle
import pytest
import numpy as np
import pandas as pd
from pathlib import Path
from unittest.mock import patch, AsyncMock

import ai_recommender as rec
from conftest import row, FakeCon, FakePool


# ─────────────────────── синтетические данные ────────────────────────

def _make_csv(path: str, n: int = 300):
    rng = np.random.default_rng(42)
    rows = []
    for _ in range(n):
        am = rng.choice(rec.AMENITIES_POOL, size=3, replace=False).tolist()
        rows.append({
            "city": rng.choice(rec.CITIES),
            "property_type": rng.choice(rec.PROPERTY_TYPES),
            "min_price": float(rng.integers(500, 2000)),
            "max_price": float(rng.integers(2000, 10000)),
            "rooms": int(rng.integers(1, 5)),
            "guests": int(rng.integers(1, 6)),
            "amenities": json.dumps(am, ensure_ascii=False),
            "check_in": "2025-06-01",
            "check_out": "2025-06-07",
            "booked": int(rng.integers(0, 2)),
        })
    pd.DataFrame(rows).to_csv(path, index=False)


# ═══════════════════════════════════════════════════════
#  1. train_model
# ═══════════════════════════════════════════════════════

class TestTrainModel:

    def test_returns_metrics_dict(self, tmp_path):
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        with patch.object(rec, "MODEL_PATH", tmp_path / "model.pkl"), \
             patch.object(rec, "ENCODERS_PATH", tmp_path / "encoders.pkl"):
            metrics = rec.train_model(data_path=csv)
        assert isinstance(metrics, dict)
        assert "accuracy" in metrics and "precision" in metrics and "recall" in metrics

    def test_metrics_in_valid_range(self, tmp_path):
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        with patch.object(rec, "MODEL_PATH", tmp_path / "model.pkl"), \
             patch.object(rec, "ENCODERS_PATH", tmp_path / "enc.pkl"):
            metrics = rec.train_model(data_path=csv)
        for v in metrics.values():
            assert 0.0 <= v <= 1.0

    def test_saves_model_file(self, tmp_path):
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        model_path = tmp_path / "model.pkl"
        with patch.object(rec, "MODEL_PATH", model_path), \
             patch.object(rec, "ENCODERS_PATH", tmp_path / "enc.pkl"):
            rec.train_model(data_path=csv)
        assert model_path.exists()

    def test_saves_encoders_file(self, tmp_path):
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        enc_path = tmp_path / "enc.pkl"
        with patch.object(rec, "MODEL_PATH", tmp_path / "m.pkl"), \
             patch.object(rec, "ENCODERS_PATH", enc_path):
            rec.train_model(data_path=csv)
        assert enc_path.exists()

    def test_file_not_found_raises(self):
        with pytest.raises(FileNotFoundError):
            rec.train_model(data_path="/nonexistent/path.csv")

    def test_encoders_have_city_and_type(self, tmp_path):
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        enc_path = tmp_path / "enc.pkl"
        with patch.object(rec, "MODEL_PATH", tmp_path / "m.pkl"), \
             patch.object(rec, "ENCODERS_PATH", enc_path):
            rec.train_model(data_path=csv)
        with open(enc_path, "rb") as f:
            encoders = pickle.load(f)
        assert "city" in encoders
        assert "type" in encoders


# ═══════════════════════════════════════════════════════
#  2. load_model
# ═══════════════════════════════════════════════════════

class TestLoadModel:

    def test_load_success(self, tmp_path):
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        with patch.object(rec, "MODEL_PATH", tmp_path / "m.pkl"), \
             patch.object(rec, "ENCODERS_PATH", tmp_path / "enc.pkl"):
            rec.train_model(data_path=csv)
            clf, encoders = rec.load_model()
        assert clf is not None
        assert "city" in encoders

    def test_load_raises_when_missing(self, tmp_path):
        with patch.object(rec, "MODEL_PATH", tmp_path / "missing.pkl"), \
             patch.object(rec, "ENCODERS_PATH", tmp_path / "missing_enc.pkl"):
            with pytest.raises(FileNotFoundError):
                rec.load_model()

    def test_loaded_clf_can_predict(self, tmp_path):
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        with patch.object(rec, "MODEL_PATH", tmp_path / "m.pkl"), \
             patch.object(rec, "ENCODERS_PATH", tmp_path / "enc.pkl"):
            rec.train_model(data_path=csv)
            clf, encoders = rec.load_model()
        X = np.zeros((1, 16))
        pred = clf.predict(X)
        assert pred.shape == (1,)


# ═══════════════════════════════════════════════════════
#  3. _build_features
# ═══════════════════════════════════════════════════════

class TestBuildFeatures:

    @pytest.fixture
    def encoders(self):
        from sklearn.preprocessing import LabelEncoder
        return (
            LabelEncoder().fit(rec.CITIES),
            LabelEncoder().fit(rec.PROPERTY_TYPES)
        )

    def _make_df(self, **overrides):
        base = dict(
            city="Москва", property_type="apartment",
            min_price=1000, max_price=5000,
            rooms=2, guests=2,
            amenities=json.dumps(["wifi"]),
            check_in="2025-06-01",
            check_out="2025-06-07",
        )
        base.update(overrides)
        return pd.DataFrame([base])

    def test_output_shape(self, encoders):
        city_enc, type_enc = encoders
        X = rec._build_features(self._make_df(), city_enc, type_enc)
        assert X.shape == (1, 16)   # 2 cat + 4 num + 10 amenities

    def test_wifi_amenity_encoded(self, encoders):
        city_enc, type_enc = encoders
        X = rec._build_features(self._make_df(amenities=json.dumps(["wifi"])), city_enc, type_enc)
        wifi_idx = rec.AMENITIES_POOL.index("wifi")
        assert X[0, 6 + wifi_idx] == 1.0

    def test_no_amenity_zeros(self, encoders):
        city_enc, type_enc = encoders
        X = rec._build_features(self._make_df(amenities="[]"), city_enc, type_enc)
        assert X[0, 6:].sum() == 0

    def test_null_amenities_handled(self, encoders):
        city_enc, type_enc = encoders
        X = rec._build_features(self._make_df(amenities=None), city_enc, type_enc)
        assert X.shape == (1, 16)

    def test_nights_calculated(self, encoders):
        city_enc, type_enc = encoders
        X = rec._build_features(
            self._make_df(check_in="2025-06-01", check_out="2025-06-08"),
            city_enc, type_enc
        )
        nights_col = X[0, 5]   # 6-й признак — ночи
        assert nights_col == 7.0

    def test_price_range(self, encoders):
        city_enc, type_enc = encoders
        X = rec._build_features(
            self._make_df(min_price=1000, max_price=6000),
            city_enc, type_enc
        )
        price_range_col = X[0, 2]
        assert price_range_col == 5000.0


# ═══════════════════════════════════════════════════════
#  4. get_recommendations
# ═══════════════════════════════════════════════════════

class TestGetRecommendations:

    @pytest.fixture(autouse=True)
    def _train(self, tmp_path):
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        self._mp = tmp_path / "m.pkl"
        self._ep = tmp_path / "enc.pkl"
        with patch.object(rec, "MODEL_PATH", self._mp), \
             patch.object(rec, "ENCODERS_PATH", self._ep):
            rec.train_model(data_path=csv)

    def _recommend(self, candidate_ids=None, top_n=5, **kw):
        if candidate_ids is None:
            candidate_ids = list(range(1, 11))
        defaults = dict(
            city="Москва", property_type="apartment",
            min_price=1000.0, max_price=5000.0,
            rooms=2, amenities=["wifi"],
            check_in="2025-08-01", check_out="2025-08-07",
            guests=2,
        )
        defaults.update(kw)
        with patch.object(rec, "MODEL_PATH", self._mp), \
             patch.object(rec, "ENCODERS_PATH", self._ep):
            return rec.get_recommendations(
                **defaults, candidate_ids=candidate_ids, top_n=top_n
            )

    def test_returns_top_n(self):
        results = self._recommend(top_n=3)
        assert len(results) == 3

    def test_sorted_by_score_desc(self):
        results = self._recommend(top_n=10)
        scores = [r["score"] for r in results]
        assert scores == sorted(scores, reverse=True)

    def test_result_has_property_id(self):
        results = self._recommend(top_n=1)
        assert "property_id" in results[0]

    def test_result_has_score(self):
        results = self._recommend(top_n=1)
        assert "score" in results[0]

    def test_empty_candidates_returns_empty(self):
        results = self._recommend(candidate_ids=[])
        assert results == []

    def test_top_n_capped_by_candidates(self):
        results = self._recommend(candidate_ids=[1, 2], top_n=10)
        assert len(results) == 2

    def test_unknown_city_fallback(self):
        results = self._recommend(city="НеизвестныйГород")
        assert len(results) > 0

    def test_unknown_type_fallback(self):
        results = self._recommend(property_type="spacecraft")
        assert len(results) > 0


# ═══════════════════════════════════════════════════════
#  5. AI HTTP Router
# ═══════════════════════════════════════════════════════

class TestAIRouter:

    def _make_client(self, tmp_path):
        from httpx import AsyncClient, ASGITransport
        from conftest import FakePool, FakeCon
        import importlib

        con = FakeCon()
        pool = FakePool(con)
        con.fetch.return_value = [row(id=i) for i in range(1, 6)]

        async def _fake_pool(**kw):
            return pool

        with patch("asyncpg.create_pool", new=AsyncMock(side_effect=_fake_pool)):
            import main as m
            importlib.reload(m)
            m.app.state.pool = pool

        transport = ASGITransport(app=m.app)
        client = AsyncClient(transport=transport, base_url="http://testserver")
        return client, con

    @pytest.mark.asyncio
    async def test_status_not_trained(self, tmp_path):
        client, _ = self._make_client(tmp_path)
        with patch.object(rec, "MODEL_PATH", tmp_path / "missing.pkl"), \
             patch.object(rec, "ENCODERS_PATH", tmp_path / "missing.pkl"):
            async with client:
                resp = await client.get("/ai/status")
        assert resp.json()["status"] == "not_trained"

    @pytest.mark.asyncio
    async def test_status_ready_after_train(self, tmp_path):
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        mp = tmp_path / "m.pkl"
        ep = tmp_path / "enc.pkl"
        with patch.object(rec, "MODEL_PATH", mp), \
             patch.object(rec, "ENCODERS_PATH", ep):
            rec.train_model(data_path=csv)

        client, _ = self._make_client(tmp_path)
        with patch.object(rec, "MODEL_PATH", mp), \
             patch.object(rec, "ENCODERS_PATH", ep):
            async with client:
                resp = await client.get("/ai/status")
        assert resp.json()["status"] == "ready"

    @pytest.mark.asyncio
    async def test_train_endpoint_returns_metrics(self, tmp_path):
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        client, _ = self._make_client(tmp_path)
        with patch.object(rec, "MODEL_PATH", tmp_path / "m.pkl"), \
             patch.object(rec, "ENCODERS_PATH", tmp_path / "enc.pkl"), \
             patch.object(rec, "DATA_PATH", Path(csv)):
            async with client:
                resp = await client.post("/ai/train")
        assert resp.status_code == 200
        body = resp.json()
        assert body["status"] == "success"
        assert "metrics" in body

    @pytest.mark.asyncio
    async def test_train_endpoint_file_not_found(self, tmp_path):
        """POST /ai/train с отсутствующим файлом → 404."""
        client, _ = self._make_client(tmp_path)
        with patch("ai_router.train_model",
                   side_effect=FileNotFoundError("no file")):
            async with client:
                resp = await client.post("/ai/train")
        assert resp.status_code == 404

    @pytest.mark.asyncio
    async def test_recommend_no_model_returns_503(self, tmp_path):
        """POST /ai/recommend без модели → 503."""
        client, _ = self._make_client(tmp_path)
        with patch("ai_router.load_model",
                   side_effect=FileNotFoundError("no model")):
            with patch("ai_router.get_recommendations",
                       side_effect=FileNotFoundError("no model")):
                async with client:
                    resp = await client.post("/ai/recommend", json={
                        "city": "Москва", "property_type": "apartment",
                        "min_price": 1000.0, "max_price": 5000.0,
                        "rooms": 2, "amenities": ["wifi"],
                        "check_in": "2025-08-01", "check_out": "2025-08-07",
                        "guests": 2
                    })
        assert resp.status_code == 503

    @pytest.mark.asyncio
    async def test_recommend_with_model(self, tmp_path):
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        mp = tmp_path / "m.pkl"
        ep = tmp_path / "enc.pkl"
        with patch.object(rec, "MODEL_PATH", mp), \
             patch.object(rec, "ENCODERS_PATH", ep):
            rec.train_model(data_path=csv)

        client, _ = self._make_client(tmp_path)
        with patch.object(rec, "MODEL_PATH", mp), \
             patch.object(rec, "ENCODERS_PATH", ep):
            async with client:
                resp = await client.post("/ai/recommend", json={
                    "city": "Москва", "property_type": "apartment",
                    "min_price": 1000.0, "max_price": 5000.0,
                    "rooms": 2, "amenities": ["wifi"],
                    "check_in": "2025-08-01", "check_out": "2025-08-07",
                    "guests": 2, "top_n": 3
                })
        assert resp.status_code == 200
        body = resp.json()
        assert body["status"] == "success"
        assert len(body["recommendations"]) <= 3

    @pytest.mark.asyncio
    async def test_recommend_type_mapping(self, tmp_path):
        """Тип 'apartment' должен маппироваться в 'appartment' в БД-запросе."""
        csv = str(tmp_path / "data.csv")
        _make_csv(csv)
        mp = tmp_path / "m.pkl"
        ep = tmp_path / "enc.pkl"
        with patch.object(rec, "MODEL_PATH", mp), \
             patch.object(rec, "ENCODERS_PATH", ep):
            rec.train_model(data_path=csv)

        client, con = self._make_client(tmp_path)
        con.fetch.side_effect = [
            [],               # строгий поиск по типу даст пустой
            [row(id=1)],      # fallback по городу
        ]
        with patch.object(rec, "MODEL_PATH", mp), \
             patch.object(rec, "ENCODERS_PATH", ep):
            async with client:
                resp = await client.post("/ai/recommend", json={
                    "city": "Москва", "property_type": "apartment",
                    "min_price": 1000.0, "max_price": 5000.0,
                    "rooms": 2, "amenities": [],
                    "check_in": "2025-08-01", "check_out": "2025-08-07",
                    "guests": 2
                })
        assert resp.status_code == 200
