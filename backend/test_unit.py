"""
test_unit.py — юнит-тесты всех FastAPI-эндпоинтов BRONIC.RU.

Покрывает: version, cities, search, register, login,
resources (CRUD), bookings, admin_api, reviews, stats, my-bookings.
"""
import sys, os
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

import pytest
import bcrypt
from unittest.mock import AsyncMock, patch
from httpx import AsyncClient

from conftest import row, _hash, SAMPLE_USER, SAMPLE_RESOURCE, SAMPLE_BOOKING, SAMPLE_REVIEW


# ═══════════════════════════════════════════════════════
#  helpers
# ═══════════════════════════════════════════════════════

def _reload_client(pool):
    """Создаёт новый клиент с заданным пулом."""
    from httpx import AsyncClient, ASGITransport
    import importlib

    async def _fake_pool(**kwargs):
        return pool

    with patch("asyncpg.create_pool", new=AsyncMock(side_effect=_fake_pool)):
        import main as m
        importlib.reload(m)
        m.app.state.pool = pool
        transport = ASGITransport(app=m.app)
        return AsyncClient(transport=transport, base_url="http://testserver")


# ═══════════════════════════════════════════════════════
#  1. Version
# ═══════════════════════════════════════════════════════

class TestVersion:

    @pytest.mark.asyncio
    async def test_version_returns_200(self, client):
        async with client:
            resp = await client.get("/v0/version")
        assert resp.status_code == 200

    @pytest.mark.asyncio
    async def test_version_has_version_field(self, client):
        async with client:
            resp = await client.get("/v0/version")
        body = resp.json()
        assert "version" in body
        assert "api" in body

    @pytest.mark.asyncio
    async def test_version_value(self, client):
        async with client:
            resp = await client.get("/v0/version")
        assert resp.json()["api"] == "bronik.ru"


# ═══════════════════════════════════════════════════════
#  2. Cities
# ═══════════════════════════════════════════════════════

class TestCities:

    @pytest.mark.asyncio
    async def test_cities_returns_list(self, client, con):
        con.fetch.return_value = [row(location="Москва"), row(location="Казань")]
        async with client:
            resp = await client.get("/cities")
        assert resp.status_code == 200
        assert resp.json()["cities"] == ["Москва", "Казань"]

    @pytest.mark.asyncio
    async def test_cities_empty_db(self, client, con):
        con.fetch.return_value = []
        async with client:
            resp = await client.get("/cities")
        assert resp.json()["cities"] == []

    @pytest.mark.asyncio
    async def test_cities_queries_active_resources(self, client, con):
        con.fetch.return_value = []
        async with client:
            await client.get("/cities")
        query = con.fetch.call_args[0][0]
        assert "is_active = TRUE" in query
        assert "location" in query


# ═══════════════════════════════════════════════════════
#  3. Search
# ═══════════════════════════════════════════════════════

class TestSearch:

    @pytest.mark.asyncio
    async def test_search_no_filters_returns_results(self, client, con):
        resource_with_reviews = row(**{**SAMPLE_RESOURCE, "review_count": 0, "avg_rating": 0.0})
        con.fetch.return_value = [resource_with_reviews]
        async with client:
            resp = await client.post("/search", json={})
        assert resp.status_code == 200
        data = resp.json()
        assert "results" in data

    @pytest.mark.asyncio
    async def test_search_with_location_filter(self, client, con):
        con.fetch.return_value = []
        async with client:
            await client.post("/search", json={"location": "Москва"})
        query = con.fetch.call_args[0][0]
        assert "ILIKE" in query

    @pytest.mark.asyncio
    async def test_search_with_type_filter(self, client, con):
        con.fetch.return_value = []
        async with client:
            await client.post("/search", json={"type": "apartment"})
        query = con.fetch.call_args[0][0]
        assert "type" in query

    @pytest.mark.asyncio
    async def test_search_with_price_range(self, client, con):
        con.fetch.return_value = []
        async with client:
            await client.post("/search", json={"min_price": 1000, "max_price": 5000})
        query = con.fetch.call_args[0][0]
        assert "base_price" in query

    @pytest.mark.asyncio
    async def test_search_with_dates_filters_booked(self, client, con):
        con.fetch.return_value = []
        async with client:
            await client.post("/search", json={
                "date_from": "2025-07-01",
                "date_to": "2025-07-07"
            })
        query = con.fetch.call_args[0][0]
        assert "CANCELLED" in query

    @pytest.mark.asyncio
    async def test_search_returns_review_fields(self, client, con):
        con.fetch.return_value = [
            row(**{**SAMPLE_RESOURCE, "review_count": 3, "avg_rating": 4.5})
        ]
        async with client:
            resp = await client.post("/search", json={})
        result = resp.json()["results"][0]
        assert "review_count" in result or "avg_rating" in result

    @pytest.mark.asyncio
    async def test_search_empty_body(self, client, con):
        con.fetch.return_value = []
        async with client:
            resp = await client.post("/search")
        assert resp.status_code == 200

    @pytest.mark.asyncio
    async def test_search_limit_50(self, client, con):
        con.fetch.return_value = []
        async with client:
            await client.post("/search", json={})
        query = con.fetch.call_args[0][0]
        assert "LIMIT 50" in query


# ═══════════════════════════════════════════════════════
#  4. Register
# ═══════════════════════════════════════════════════════

class TestRegister:

    @pytest.mark.asyncio
    async def test_register_success(self, client, con):
        con.fetch.return_value = []          # email свободен
        con.execute.return_value = None
        async with client:
            resp = await client.post("/register", json={
                "email": "new@bronic.ru",
                "password": "Secret123",
                "name": "Анна",
                "surname": "Петрова"
            })
        assert resp.status_code == 200
        assert resp.json()["message"] == "ok"

    @pytest.mark.asyncio
    async def test_register_duplicate_email(self, client, con):
        con.fetch.return_value = [SAMPLE_USER]   # email занят
        async with client:
            resp = await client.post("/register", json={
                "email": "user@bronic.ru",
                "password": "Secret123",
                "name": "A",
                "surname": "B"
            })
        assert resp.json()["message"] == "почта занята"

    @pytest.mark.asyncio
    async def test_register_password_hashed(self, client, con):
        con.fetch.return_value = []
        async with client:
            await client.post("/register", json={
                "email": "x@x.com",
                "password": "MyPlainPass",
                "name": "X", "surname": "Y"
            })
        call_args = con.execute.call_args[0]
        password_hash_in_db = call_args[2]
        assert password_hash_in_db != "MyPlainPass"
        assert len(password_hash_in_db) > 20

    @pytest.mark.asyncio
    async def test_register_stores_salt(self, client, con):
        con.fetch.return_value = []
        async with client:
            await client.post("/register", json={
                "email": "s@s.com",
                "password": "SaltTest123",
                "name": "S", "surname": "T"
            })
        call_args = con.execute.call_args[0]
        salt_in_db = call_args[3]
        assert salt_in_db and len(salt_in_db) > 10

    @pytest.mark.asyncio
    async def test_register_sets_role_user(self, client, con):
        con.fetch.return_value = []
        async with client:
            await client.post("/register", json={
                "email": "role@test.com",
                "password": "Pass123",
                "name": "R", "surname": "O"
            })
        call_args = con.execute.call_args[0]
        role_in_db = call_args[6]  # 7-й аргумент — role
        assert role_in_db == "user"


# ═══════════════════════════════════════════════════════
#  5. Login
# ═══════════════════════════════════════════════════════

class TestLogin:

    @pytest.mark.asyncio
    async def test_login_success(self, client, con):
        user = row(
            id=1, email="user@bronic.ru",
            password_hash=_hash("Pass123"),
            role="user", name="Иван", surname="Иванов"
        )
        con.fetch.return_value = [user]
        async with client:
            resp = await client.post("/login", json={
                "email": "user@bronic.ru",
                "password": "Pass123"
            })
        body = resp.json()
        assert body["success"] == "true"
        assert body["role"] == "user"
        assert body["email"] == "user@bronic.ru"

    @pytest.mark.asyncio
    async def test_login_success_returns_redirect(self, client, con):
        user = row(
            id=1, email="u@b.ru",
            password_hash=_hash("P"),
            role="user", name="A", surname="B"
        )
        con.fetch.return_value = [user]
        async with client:
            resp = await client.post("/login", json={"email": "u@b.ru", "password": "P"})
        assert "redirect" in resp.json()

    @pytest.mark.asyncio
    async def test_login_user_not_found(self, client, con):
        con.fetch.return_value = []
        async with client:
            resp = await client.post("/login", json={"email": "nobody@x.com", "password": "x"})
        assert resp.json()["message"] == "пользователь не найден"

    @pytest.mark.asyncio
    async def test_login_wrong_password(self, client, con):
        user = row(
            id=1, email="u@b.ru",
            password_hash=_hash("Correct123"),
            role="user", name="A", surname="B"
        )
        con.fetch.return_value = [user]
        async with client:
            resp = await client.post("/login", json={"email": "u@b.ru", "password": "Wrong!"})
        assert resp.json()["message"] == "неправильный логин или пароль"

    @pytest.mark.asyncio
    async def test_login_returns_name_surname(self, client, con):
        user = row(
            id=2, email="v@b.ru",
            password_hash=_hash("Pw"),
            role="admin", name="Василий", surname="Пупкин"
        )
        con.fetch.return_value = [user]
        async with client:
            resp = await client.post("/login", json={"email": "v@b.ru", "password": "Pw"})
        body = resp.json()
        assert body["name"] == "Василий"
        assert body["surname"] == "Пупкин"


# ═══════════════════════════════════════════════════════
#  6. Resources
# ═══════════════════════════════════════════════════════

class TestResources:

    @pytest.mark.asyncio
    async def test_get_resource_success(self, client, con):
        con.fetchrow.return_value = SAMPLE_RESOURCE
        async with client:
            resp = await client.get("/resources/42")
        assert resp.status_code == 200
        body = resp.json()
        assert body["id"] == 42
        assert body["name"] == "Уютная студия"

    @pytest.mark.asyncio
    async def test_get_resource_not_found(self, client, con):
        con.fetchrow.return_value = None
        async with client:
            resp = await client.get("/resources/9999")
        assert resp.status_code == 404

    @pytest.mark.asyncio
    async def test_create_resource_success(self, client, con):
        con.fetchrow.return_value = row(id=99)
        async with client:
            resp = await client.post("/resources", json={
                "name": "Новая дача",
                "type": "dacha",
                "base_price": 5000,
                "address": "ул. Мира, 5",
                "location": "Казань"
            })
        assert resp.status_code == 200
        body = resp.json()
        assert body["id"] == 99
        assert "успешно" in body["message"]

    @pytest.mark.asyncio
    async def test_create_resource_defaults(self, client, con):
        con.fetchrow.return_value = row(id=1)
        async with client:
            resp = await client.post("/resources", json={})
        assert resp.status_code == 200
        assert "id" in resp.json()

    @pytest.mark.asyncio
    async def test_get_resource_local_image_mapping(self, client, con):
        """Внешние URL изображений заменяются на локальные пути."""
        res = row(**{**SAMPLE_RESOURCE, "image_url": "https://unsplash.com/photo.jpg"})
        con.fetchrow.return_value = res
        async with client:
            resp = await client.get("/resources/1")
        img = resp.json().get("image_url", "")
        assert "unsplash.com" not in img


# ═══════════════════════════════════════════════════════
#  7. Bookings
# ═══════════════════════════════════════════════════════

class TestBookings:

    @pytest.mark.asyncio
    async def test_create_booking_success(self, client, con):
        con.fetchrow.side_effect = [None, row(id=10)]  # нет конфликта, затем INSERT RETURNING
        async with client:
            resp = await client.post("/bookings", json={
                "resource_id": 42,
                "user_id": 1,
                "start_time": "2025-08-01",
                "end_time": "2025-08-07",
                "price": 21000
            })
        assert resp.status_code == 200
        body = resp.json()
        assert body["success"] is True
        assert body["id"] == 10

    @pytest.mark.asyncio
    async def test_create_booking_conflict(self, client, con):
        con.fetchrow.return_value = row(id=5)   # конфликт
        async with client:
            resp = await client.post("/bookings", json={
                "resource_id": 42,
                "user_id": 1,
                "start_time": "2025-08-01",
                "end_time": "2025-08-07",
                "price": 0
            })
        assert resp.json()["success"] is False
        assert "забронирован" in resp.json()["error"]

    @pytest.mark.asyncio
    async def test_create_booking_missing_dates(self, client, con):
        async with client:
            resp = await client.post("/bookings", json={
                "resource_id": 1,
                "user_id": 1,
                "price": 1000
            })
        assert "error" in resp.json()

    @pytest.mark.asyncio
    async def test_create_booking_accepts_checkin_format(self, client, con):
        """Фронт отправляет поля checkin/checkout."""
        con.fetchrow.side_effect = [None, row(id=20)]
        async with client:
            resp = await client.post("/bookings", json={
                "resource_id": 1,
                "user_id": 1,
                "checkin": "2025-09-01",
                "checkout": "2025-09-05",
                "price": 5000
            })
        assert resp.json().get("success") is True

    @pytest.mark.asyncio
    async def test_create_booking_dd_mm_yyyy_format(self, client, con):
        """Фронт может слать DD.MM.YYYY."""
        con.fetchrow.side_effect = [None, row(id=21)]
        async with client:
            resp = await client.post("/bookings", json={
                "resource_id": 1,
                "user_id": 1,
                "start_time": "15.07.2025",
                "end_time": "20.07.2025",
                "price": 1000
            })
        assert resp.json().get("success") is True

    @pytest.mark.asyncio
    async def test_my_bookings_returns_list(self, client, con):
        con.fetch.return_value = [SAMPLE_BOOKING]
        async with client:
            resp = await client.get("/my-bookings?user_id=1")
        assert resp.status_code == 200
        assert "bookings" in resp.json()
        assert len(resp.json()["bookings"]) == 1

    @pytest.mark.asyncio
    async def test_my_bookings_empty(self, client, con):
        con.fetch.return_value = []
        async with client:
            resp = await client.get("/my-bookings?user_id=999")
        assert resp.json()["bookings"] == []


# ═══════════════════════════════════════════════════════
#  8. Reviews
# ═══════════════════════════════════════════════════════

class TestReviews:

    @pytest.mark.asyncio
    async def test_get_reviews_success(self, client, con):
        con.fetch.return_value = [SAMPLE_REVIEW]
        async with client:
            resp = await client.get("/reviews/42")
        assert resp.status_code == 200
        assert "reviews" in resp.json()
        assert len(resp.json()["reviews"]) == 1

    @pytest.mark.asyncio
    async def test_get_reviews_empty(self, client, con):
        con.fetch.return_value = []
        async with client:
            resp = await client.get("/reviews/1")
        assert resp.json()["reviews"] == []

    @pytest.mark.asyncio
    async def test_add_review_success(self, client, con):
        async with client:
            resp = await client.post("/reviews", json={
                "resource_id": 42,
                "author_name": "Мария",
                "rating": 5,
                "comment": "Отлично!"
            })
        assert resp.status_code == 200
        assert resp.json()["success"] is True

    @pytest.mark.asyncio
    async def test_add_review_missing_resource_id(self, client, con):
        async with client:
            resp = await client.post("/reviews", json={
                "author_name": "X",
                "rating": 3
            })
        assert "error" in resp.json()

    @pytest.mark.asyncio
    async def test_add_review_default_author(self, client, con):
        """author_name по умолчанию = 'Гость'."""
        async with client:
            await client.post("/reviews", json={"resource_id": 1, "rating": 4})
        call_args = con.execute.call_args[0]
        assert call_args[2] == "Гость"

    @pytest.mark.asyncio
    async def test_get_reviews_created_at_iso(self, client, con):
        """created_at должен быть в ISO-формате."""
        from conftest import row
        from datetime import datetime
        rev = row(
            id=1, resource_id=1,
            author_name="A", rating=5, comment="B",
            created_at=datetime(2025, 1, 15, 12, 0)
        )
        con.fetch.return_value = [rev]
        async with client:
            resp = await client.get("/reviews/1")
        assert "2025-01-15" in resp.json()["reviews"][0]["created_at"]


# ═══════════════════════════════════════════════════════
#  9. Stats
# ═══════════════════════════════════════════════════════

class TestStats:

    @pytest.mark.asyncio
    async def test_stats_returns_all_fields(self, client, con):
        con.fetchval.side_effect = [10, 5, 20, 150000.0, 8]
        async with client:
            resp = await client.get("/stats")
        assert resp.status_code == 200
        body = resp.json()
        assert all(k in body for k in ["users", "resources", "bookings", "revenue", "reviews"])

    @pytest.mark.asyncio
    async def test_stats_revenue_is_float(self, client, con):
        con.fetchval.side_effect = [1, 1, 1, 12345.67, 0]
        async with client:
            resp = await client.get("/stats")
        assert isinstance(resp.json()["revenue"], float)

    @pytest.mark.asyncio
    async def test_stats_reviews_fallback_on_error(self, client, con):
        """Если таблица reviews не существует — stats не падает."""
        async def _side_effect(*args, **kwargs):
            call_count = con.fetchval.call_count
            if call_count == 5:
                raise Exception("relation reviews does not exist")
            return call_count * 5

        con.fetchval.side_effect = [10, 5, 20, 50000.0, Exception("no reviews table")]
        async with client:
            resp = await client.get("/stats")
        # Должен вернуть что-то, а не 500
        assert resp.status_code == 200


# ═══════════════════════════════════════════════════════
#  10. Admin API
# ═══════════════════════════════════════════════════════

class TestAdminApi:

    @pytest.mark.asyncio
    async def test_get_all_users(self, client, con):
        con.fetch.return_value = [
            row(id=1, email="a@b.ru", name="A", surname="B",
                role="user", created_at=None)
        ]
        async with client:
            resp = await client.post("/admin_api", json={
                "action": "get_all", "table": "users"
            })
        assert resp.status_code == 200
        assert "results" in resp.json()

    @pytest.mark.asyncio
    async def test_get_all_resources(self, client, con):
        con.fetch.return_value = [SAMPLE_RESOURCE]
        async with client:
            resp = await client.post("/admin_api", json={
                "action": "get_all", "table": "resources"
            })
        assert resp.status_code == 200

    @pytest.mark.asyncio
    async def test_get_all_bookings(self, client, con):
        con.fetch.return_value = [SAMPLE_BOOKING]
        async with client:
            resp = await client.post("/admin_api", json={
                "action": "get_all", "table": "bookings"
            })
        assert resp.status_code == 200

    @pytest.mark.asyncio
    async def test_invalid_table_blocked(self, client, con):
        async with client:
            resp = await client.post("/admin_api", json={
                "action": "get_all", "table": "secrets"
            })
        assert "error" in resp.json()
        assert resp.json()["error"] == "invalid table"

    @pytest.mark.asyncio
    async def test_delete_user(self, client, con):
        async with client:
            resp = await client.post("/admin_api", json={
                "action": "delete", "table": "users", "id": 1
            })
        assert resp.status_code == 200
        assert resp.json()["success"] is True

    @pytest.mark.asyncio
    async def test_delete_without_id(self, client, con):
        async with client:
            resp = await client.post("/admin_api", json={
                "action": "delete", "table": "users"
            })
        assert "error" in resp.json()

    @pytest.mark.asyncio
    async def test_update_user(self, client, con):
        async with client:
            resp = await client.post("/admin_api", json={
                "action": "update",
                "table": "users",
                "id": 1,
                "fields": {"name": "Новое Имя"}
            })
        assert resp.status_code == 200
        assert resp.json()["success"] is True

    @pytest.mark.asyncio
    async def test_update_without_fields(self, client, con):
        async with client:
            resp = await client.post("/admin_api", json={
                "action": "update", "table": "users", "id": 1
            })
        assert "error" in resp.json()

    @pytest.mark.asyncio
    async def test_invalid_action(self, client, con):
        async with client:
            resp = await client.post("/admin_api", json={
                "action": "drop_table", "table": "users"
            })
        assert "error" in resp.json()


# ═══════════════════════════════════════════════════════
#  11. parse_date helper
# ═══════════════════════════════════════════════════════

class TestParseDate:

    def test_iso_format(self):
        import importlib
        import main as m
        dt = m.parse_date("2025-07-15")
        assert dt.year == 2025
        assert dt.month == 7
        assert dt.day == 15

    def test_russian_format(self):
        import main as m
        dt = m.parse_date("15.07.2025")
        assert dt.day == 15
        assert dt.month == 7

    def test_invalid_raises(self):
        import main as m
        with pytest.raises(ValueError):
            m.parse_date("not-a-date")
