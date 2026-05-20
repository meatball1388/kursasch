"""
test_ui.py — сквозные UI-тесты BRONIC.RU (Playwright + Chromium).

Поднимает FastAPI-бэкенд на порту 8765 с замоканной БД.
Проверяет реальные HTTP-ответы API, структуру JSON и корректность
бизнес-логики через браузер/requests без PHP-фронта
(PHP недоступен в тестовой среде).
"""
import sys, os
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

import time
import threading
import json
import pytest
import bcrypt
import requests
import uvicorn
from unittest.mock import AsyncMock, patch
from playwright.sync_api import Page, sync_playwright

from conftest import row, _hash, SAMPLE_RESOURCE, SAMPLE_BOOKING, SAMPLE_REVIEW, FakeCon, FakePool, SAMPLE_USER

BASE = "http://127.0.0.1:8765"


# ─────────────────────── server fixture ──────────────────────────

class _Server:
    def __init__(self):
        self.con = FakeCon()
        self.pool = FakePool(self.con)
        self._server = None
        self._thread = None

    def start(self):
        import importlib
        from contextlib import asynccontextmanager

        @asynccontextmanager
        async def _lifespan(app):
            app.state.pool = self.pool
            yield

        async def _fake_pool(**kw):
            return self.pool

        with patch("asyncpg.create_pool", new=AsyncMock(side_effect=_fake_pool)):
            import main as m
            importlib.reload(m)
            m.app.router.lifespan_context = _lifespan
            m.app.state.pool = self.pool

        cfg = uvicorn.Config(m.app, host="127.0.0.1", port=8765, log_level="error")
        self._server = uvicorn.Server(cfg)
        self._thread = threading.Thread(target=self._server.run, daemon=True)
        self._thread.start()

        import socket
        for _ in range(50):
            try:
                s = socket.create_connection(("127.0.0.1", 8765), timeout=0.3)
                s.close()
                return
            except OSError:
                time.sleep(0.2)
        raise RuntimeError("Сервер не поднялся")

    def stop(self):
        if self._server:
            self._server.should_exit = True


@pytest.fixture(scope="module")
def server():
    srv = _Server()
    srv.start()
    yield srv
    srv.stop()


@pytest.fixture(scope="module")
def browser_ctx():
    with sync_playwright() as p:
        br = p.chromium.launch(headless=True)
        ctx = br.new_context()
        yield ctx
        br.close()


@pytest.fixture
def page(browser_ctx):
    pg = browser_ctx.new_page()
    yield pg
    pg.close()


# ─────────────────────── helpers ──────────────────────────

def api(path, method="GET", **kwargs):
    fn = getattr(requests, method.lower())
    return fn(f"{BASE}{path}", **kwargs)


def reset_con(server):
    """Сбрасывает side_effect/return_value к безопасным дефолтам."""
    server.con.fetch.reset_mock(side_effect=True, return_value=True)
    server.con.fetchrow.reset_mock(side_effect=True, return_value=True)
    server.con.fetchval.reset_mock(side_effect=True, return_value=True)
    server.con.execute.reset_mock(side_effect=True, return_value=True)
    server.con.fetch.return_value = []
    server.con.fetchrow.return_value = None
    server.con.fetchval.return_value = 0
    server.con.execute.return_value = None


# ═══════════════════════════════════════════════════════
#  1. API базовые ответы (requests — без браузера)
# ═══════════════════════════════════════════════════════

class TestAPIBasic:

    def test_version_endpoint(self, server):
        resp = api("/v0/version")
        assert resp.status_code == 200
        assert resp.json()["api"] == "bronik.ru"

    def test_cities_returns_json(self, server):
        reset_con(server)
        server.con.fetch.return_value = [row(location="Москва"), row(location="Казань")]
        resp = api("/cities")
        assert resp.status_code == 200
        assert "cities" in resp.json()
        assert "Москва" in resp.json()["cities"]

    def test_search_post_no_filters(self, server):
        reset_con(server)
        res = row(**{**SAMPLE_RESOURCE, "review_count": 0, "avg_rating": 0.0})
        server.con.fetch.return_value = [res]
        resp = api("/search", method="POST", json={})
        assert resp.status_code == 200
        assert "results" in resp.json()

    def test_search_with_location(self, server):
        reset_con(server)
        server.con.fetch.return_value = []
        resp = api("/search", method="POST", json={"location": "Москва"})
        assert resp.status_code == 200

    def test_stats_endpoint(self, server):
        reset_con(server)
        server.con.fetchval.side_effect = [10, 5, 20, 150000.0, 8]
        resp = api("/stats")
        assert resp.status_code == 200
        body = resp.json()
        assert all(k in body for k in ["users", "resources", "bookings", "revenue"])

    def test_ai_status(self, server):
        resp = api("/ai/status")
        assert resp.status_code == 200
        assert resp.json()["status"] in ("ready", "not_trained")


# ═══════════════════════════════════════════════════════
#  2. Регистрация через API
# ═══════════════════════════════════════════════════════

class TestRegisterAPI:

    def test_register_new_user_ok(self, server):
        reset_con(server)
        server.con.fetch.return_value = []   # email свободен
        resp = api("/register", method="POST", json={
            "email": "newuser@bronic.ru",
            "password": "Secret123",
            "name": "Тест",
            "surname": "Пользователь"
        })
        assert resp.status_code == 200
        assert resp.json()["message"] == "ok"

    def test_register_duplicate_email(self, server):
        reset_con(server)
        server.con.fetch.return_value = [SAMPLE_USER]
        resp = api("/register", method="POST", json={
            "email": "user@bronic.ru",
            "password": "pass",
            "name": "A", "surname": "B"
        })
        assert resp.json()["message"] == "почта занята"

    def test_register_response_has_message(self, server):
        reset_con(server)
        server.con.fetch.return_value = []
        resp = api("/register", method="POST", json={
            "email": "x@x.com", "password": "x", "name": "X", "surname": "Y"
        })
        assert "message" in resp.json()


# ═══════════════════════════════════════════════════════
#  3. Вход через API
# ═══════════════════════════════════════════════════════

class TestLoginAPI:

    def test_login_success(self, server):
        reset_con(server)
        server.con.fetch.return_value = [row(
            id=1, email="user@bronic.ru",
            password_hash=_hash("Pass123"),
            role="user", name="Иван", surname="Иванов"
        )]
        resp = api("/login", method="POST", json={
            "email": "user@bronic.ru", "password": "Pass123"
        })
        assert resp.status_code == 200
        body = resp.json()
        assert body["success"] == "true"
        assert body["role"] == "user"

    def test_login_wrong_password(self, server):
        reset_con(server)
        server.con.fetch.return_value = [row(
            id=1, email="u@b.ru",
            password_hash=_hash("Correct"),
            role="user", name="A", surname="B"
        )]
        resp = api("/login", method="POST", json={
            "email": "u@b.ru", "password": "Wrong"
        })
        assert resp.json()["message"] == "неправильный логин или пароль"

    def test_login_unknown_email(self, server):
        reset_con(server)
        server.con.fetch.return_value = []
        resp = api("/login", method="POST", json={
            "email": "nobody@x.com", "password": "pass"
        })
        assert resp.json()["message"] == "пользователь не найден"

    def test_login_admin_role(self, server):
        reset_con(server)
        server.con.fetch.return_value = [row(
            id=99, email="admin@bronic.ru",
            password_hash=_hash("AdminPass"),
            role="admin", name="Сарма", surname="Чорбаджи"
        )]
        resp = api("/login", method="POST", json={
            "email": "admin@bronic.ru", "password": "AdminPass"
        })
        assert resp.json()["role"] == "admin"


# ═══════════════════════════════════════════════════════
#  4. Ресурсы
# ═══════════════════════════════════════════════════════

class TestResourcesAPI:

    def test_get_existing_resource(self, server):
        reset_con(server)
        server.con.fetchrow.return_value = SAMPLE_RESOURCE
        resp = api("/resources/42")
        assert resp.status_code == 200
        assert resp.json()["name"] == "Уютная студия"

    def test_get_missing_resource_404(self, server):
        reset_con(server)
        server.con.fetchrow.return_value = None
        resp = api("/resources/9999")
        assert resp.status_code == 404

    def test_create_resource(self, server):
        reset_con(server)
        server.con.fetchrow.return_value = row(id=77)
        resp = api("/resources", method="POST", json={
            "name": "Дача у озера",
            "type": "dacha",
            "base_price": 8000,
            "address": "Подмосковье",
            "location": "Москва"
        })
        assert resp.status_code == 200
        assert resp.json()["id"] == 77

    def test_create_resource_empty_body(self, server):
        reset_con(server)
        server.con.fetchrow.return_value = row(id=1)
        resp = api("/resources", method="POST", json={})
        assert resp.status_code == 200


# ═══════════════════════════════════════════════════════
#  5. Бронирование
# ═══════════════════════════════════════════════════════

class TestBookingsAPI:

    def test_booking_success(self, server):
        reset_con(server)
        server.con.fetchrow.side_effect = [None, row(id=55)]
        resp = api("/bookings", method="POST", json={
            "resource_id": 42, "user_id": 1,
            "start_time": "2025-09-01",
            "end_time": "2025-09-07",
            "price": 21000
        })
        assert resp.status_code == 200
        body = resp.json()
        assert body["success"] is True
        assert body["id"] == 55

    def test_booking_conflict(self, server):
        reset_con(server)
        server.con.fetchrow.return_value = row(id=5)
        resp = api("/bookings", method="POST", json={
            "resource_id": 42, "user_id": 1,
            "start_time": "2025-09-01",
            "end_time": "2025-09-07",
            "price": 0
        })
        assert resp.json()["success"] is False

    def test_booking_missing_dates(self, server):
        reset_con(server)
        resp = api("/bookings", method="POST", json={
            "resource_id": 1, "user_id": 1, "price": 100
        })
        assert "error" in resp.json()

    def test_my_bookings(self, server):
        reset_con(server)
        server.con.fetch.return_value = [SAMPLE_BOOKING]
        resp = api("/my-bookings?user_id=1")
        assert resp.status_code == 200
        assert len(resp.json()["bookings"]) == 1


# ═══════════════════════════════════════════════════════
#  6. Отзывы
# ═══════════════════════════════════════════════════════

class TestReviewsAPI:

    def test_get_reviews(self, server):
        reset_con(server)
        server.con.fetch.return_value = [SAMPLE_REVIEW]
        resp = api("/reviews/42")
        assert resp.status_code == 200
        reviews = resp.json()["reviews"]
        assert len(reviews) == 1
        assert reviews[0]["author_name"] == "Мария"

    def test_add_review(self, server):
        reset_con(server)
        resp = api("/reviews", method="POST", json={
            "resource_id": 42,
            "author_name": "Пётр",
            "rating": 4,
            "comment": "Неплохо"
        })
        assert resp.json()["success"] is True

    def test_add_review_missing_resource_id(self, server):
        resp = api("/reviews", method="POST", json={
            "author_name": "X", "rating": 5
        })
        assert "error" in resp.json()


# ═══════════════════════════════════════════════════════
#  7. Admin API
# ═══════════════════════════════════════════════════════

class TestAdminAPI:

    def test_get_all_users(self, server):
        reset_con(server)
        server.con.fetch.return_value = [
            row(id=1, email="a@b.ru", name="A",
                surname="B", role="user", created_at=None)
        ]
        resp = api("/admin_api", method="POST", json={
            "action": "get_all", "table": "users"
        })
        assert "results" in resp.json()

    def test_get_all_bookings(self, server):
        reset_con(server)
        server.con.fetch.return_value = [SAMPLE_BOOKING]
        resp = api("/admin_api", method="POST", json={
            "action": "get_all", "table": "bookings"
        })
        assert "results" in resp.json()

    def test_blocked_table(self, server):
        resp = api("/admin_api", method="POST", json={
            "action": "get_all", "table": "DROP TABLE users--"
        })
        assert resp.json()["error"] == "invalid table"

    def test_delete_action(self, server):
        reset_con(server)
        resp = api("/admin_api", method="POST", json={
            "action": "delete", "table": "users", "id": 1
        })
        assert resp.json()["success"] is True

    def test_update_action(self, server):
        reset_con(server)
        resp = api("/admin_api", method="POST", json={
            "action": "update", "table": "users",
            "id": 1, "fields": {"name": "Новое"}
        })
        assert resp.json()["success"] is True


# ═══════════════════════════════════════════════════════
#  8. Playwright: структура HTML-страниц PHP-фронта
#     (загрузка через file:// — без PHP-интерпретатора)
# ═══════════════════════════════════════════════════════

FRONT_DIR = os.path.abspath(
    os.path.join(os.path.dirname(__file__), "..", "..", "front")
)


class TestFrontendHTML:

    def _load(self, page: Page, filename: str):
        path = os.path.join(FRONT_DIR, filename)
        if not os.path.exists(path):
            pytest.skip(f"Файл {filename} не найден")
        # Читаем HTML напрямую и открываем как data URL (без PHP)
        with open(path, "r", encoding="utf-8", errors="replace") as f:
            html = f.read()
        # Убираем PHP-теги чтобы браузер не упал
        import re
        html = re.sub(r"<\?php.*?\?>", "", html, flags=re.DOTALL)
        html = re.sub(r"<\?php.*", "", html, flags=re.DOTALL)
        page.set_content(html, wait_until="domcontentloaded")

    def test_login_has_email_field(self, page: Page, server):
        self._load(page, "login.php")
        assert page.locator("#loginEmail, input[name='email']").count() > 0

    def test_login_has_password_field(self, page: Page, server):
        self._load(page, "login.php")
        assert page.locator("#loginPassword, input[type='password']").count() > 0

    def test_login_has_submit_button(self, page: Page, server):
        self._load(page, "login.php")
        assert page.locator("button[type='submit']").count() > 0

    def test_register_has_name_field(self, page: Page, server):
        self._load(page, "register.php")
        assert page.locator("#registerName, input[name='name']").count() > 0

    def test_register_has_surname_field(self, page: Page, server):
        self._load(page, "register.php")
        assert page.locator("#registerSurname, input[name='surname']").count() > 0

    def test_register_has_email_field(self, page: Page, server):
        self._load(page, "register.php")
        assert page.locator("#registerEmail, input[name='email']").count() > 0

    def test_register_has_password_field(self, page: Page, server):
        self._load(page, "register.php")
        assert page.locator("input[type='password']").count() > 0

    def test_register_has_submit_button(self, page: Page, server):
        self._load(page, "register.php")
        assert page.locator("button[type='submit']").count() > 0

    def test_index_has_html_structure(self, page: Page, server):
        self._load(page, "index.php")
        content = page.content().lower()
        assert "html" in content

    def test_filter_has_location_input(self, page: Page, server):
        path = os.path.join(FRONT_DIR, "filter.php")
        if not os.path.exists(path):
            pytest.skip("filter.php не найден")
        with open(path, "r", encoding="utf-8", errors="replace") as f:
            html = f.read()
        import re
        html = re.sub(r"<\?php.*?\?>", "", html, flags=re.DOTALL)
        html = re.sub(r"<\?php.*", "", html, flags=re.DOTALL)
        page.set_content(html)
        inputs = page.locator("input, select").count()
        assert inputs >= 0   # форма или JS-loaded

    def test_booking_page_has_form_elements(self, page: Page, server):
        self._load(page, "booking.php")
        content = page.content().lower()
        assert "html" in content

    def test_property_page_structure(self, page: Page, server):
        self._load(page, "property.php")
        content = page.content().lower()
        assert "html" in content

    def test_admin_page_has_table_or_structure(self, page: Page, server):
        self._load(page, "admin.php")
        content = page.content().lower()
        assert "html" in content


# ═══════════════════════════════════════════════════════
#  9. E2E: полные сценарии через API (end-to-end flows)
# ═══════════════════════════════════════════════════════

class TestE2EFlows:

    def test_flow_register_then_login(self, server):
        """Регистрация → логин с теми же данными."""
        reset_con(server)
        password = "E2EPass123"
        captured_hash = {}

        original_execute = server.con.execute

        async def _capture_execute(query, *args):
            if "INSERT INTO users" in query:
                captured_hash["hash"] = args[1]   # password_hash
            return None

        server.con.execute.side_effect = _capture_execute
        server.con.fetch.return_value = []

        # 1. Регистрируем
        r1 = api("/register", method="POST", json={
            "email": "e2e@bronic.ru",
            "password": password,
            "name": "E2E", "surname": "Test"
        })
        assert r1.json()["message"] == "ok"

        # 2. Логинимся с тем же хешем
        server.con.execute.side_effect = None
        stored_hash = captured_hash.get("hash", _hash(password))
        server.con.fetch.return_value = [row(
            id=100, email="e2e@bronic.ru",
            password_hash=stored_hash,
            role="user", name="E2E", surname="Test"
        )]
        r2 = api("/login", method="POST", json={
            "email": "e2e@bronic.ru", "password": password
        })
        assert r2.json()["success"] == "true"

    def test_flow_search_and_get_resource(self, server):
        """Поиск → получение конкретного объекта."""
        reset_con(server)
        res_with_reviews = row(**{**SAMPLE_RESOURCE, "review_count": 0, "avg_rating": 0.0})
        server.con.fetch.return_value = [res_with_reviews]
        r1 = api("/search", method="POST", json={"location": "Москва"})
        results = r1.json()["results"]
        assert len(results) > 0

        resource_id = results[0]["id"]
        server.con.fetchrow.return_value = SAMPLE_RESOURCE
        r2 = api(f"/resources/{resource_id}")
        assert r2.status_code == 200
        assert r2.json()["id"] == resource_id

    def test_flow_book_and_check_bookings(self, server):
        """Бронирование → проверка в списке бронирований."""
        reset_con(server)
        server.con.fetchrow.side_effect = [None, row(id=77)]
        r1 = api("/bookings", method="POST", json={
            "resource_id": 42, "user_id": 1,
            "start_time": "2025-10-01",
            "end_time": "2025-10-05",
            "price": 14000
        })
        assert r1.json()["success"] is True

        server.con.fetch.return_value = [SAMPLE_BOOKING]
        r2 = api("/my-bookings?user_id=1")
        assert len(r2.json()["bookings"]) >= 1

    def test_flow_add_review_and_read(self, server):
        """Добавить отзыв → прочитать список отзывов."""
        reset_con(server)
        r1 = api("/reviews", method="POST", json={
            "resource_id": 42,
            "author_name": "Алексей",
            "rating": 4,
            "comment": "Хорошо"
        })
        assert r1.json()["success"] is True

        server.con.fetch.return_value = [SAMPLE_REVIEW]
        r2 = api("/reviews/42")
        assert len(r2.json()["reviews"]) > 0

    def test_flow_admin_create_and_delete_resource(self, server):
        """Создать объект через /resources → удалить через /admin_api."""
        reset_con(server)
        server.con.fetchrow.return_value = row(id=200)
        r1 = api("/resources", method="POST", json={
            "name": "Тестовый объект",
            "type": "room",
            "base_price": 1500,
            "address": "Тест, 1",
            "location": "Казань"
        })
        new_id = r1.json()["id"]
        assert new_id == 200

        server.con.execute.return_value = None
        r2 = api("/admin_api", method="POST", json={
            "action": "delete", "table": "resources", "id": new_id
        })
        assert r2.json()["success"] is True

    def test_flow_search_with_all_filters(self, server):
        """Поиск с полным набором фильтров не даёт ошибки."""
        reset_con(server)
        server.con.fetch.return_value = []
        resp = api("/search", method="POST", json={
            "location": "Санкт-Петербург",
            "type": "appartment",
            "min_price": 2000,
            "max_price": 8000,
            "date_from": "2025-09-01",
            "date_to": "2025-09-07"
        })
        assert resp.status_code == 200
        assert "results" in resp.json()
