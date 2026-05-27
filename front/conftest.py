"""
conftest.py — общие фикстуры и хелперы для всех тестов BRONIC.RU.

Подменяет asyncpg.create_pool пулом-заглушкой, чтобы тесты
работали без реальной PostgreSQL.
"""
import sys
import os
import json
import bcrypt
import pytest
from unittest.mock import AsyncMock, MagicMock, patch
from datetime import datetime, date

# Корень бэкенда в PYTHONPATH
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))


# ─────────────────────── helpers ────────────────────────

def _hash(pw: str) -> str:
    salt = bcrypt.gensalt()
    return bcrypt.hashpw(pw.encode("utf8"), salt).decode("utf8")


class FakeRecord(dict):
    """dict-подобный объект, имитирующий asyncpg.Record."""
    def __getattr__(self, item):
        try:
            return self[item]
        except KeyError:
            raise AttributeError(item)


def row(**kwargs) -> FakeRecord:
    return FakeRecord(kwargs)


# ─────────────────────── mock pool/connection ────────────────────────

class FakeCon:
    """Имитирует asyncpg-соединение (из пула)."""
    def __init__(self):
        self.fetch     = AsyncMock(return_value=[])
        self.fetchrow  = AsyncMock(return_value=None)
        self.fetchval  = AsyncMock(return_value=0)
        self.execute   = AsyncMock(return_value=None)

    async def __aenter__(self):
        return self

    async def __aexit__(self, *_):
        pass


class FakePool:
    """Имитирует asyncpg.Pool."""
    def __init__(self, con: FakeCon):
        self._con = con

    def acquire(self):
        return self._con   # FakeCon поддерживает async context manager

    async def close(self):
        pass


@pytest.fixture
def con():
    return FakeCon()


@pytest.fixture
def pool(con):
    return FakePool(con)


# ─────────────────────── FastAPI test client ────────────────────────

@pytest.fixture
def client(pool):
    """httpx.AsyncClient с FastAPI-приложением и мок-пулом."""
    from httpx import AsyncClient, ASGITransport
    import importlib

    async def fake_create_pool(**kwargs):
        return pool

    with patch("asyncpg.create_pool", new=AsyncMock(side_effect=fake_create_pool)):
        import main as m
        importlib.reload(m)
        m.app.state.pool = pool
        transport = ASGITransport(app=m.app)
        return AsyncClient(transport=transport, base_url="http://testserver")


# ─────────────────────── sample data ────────────────────────

SAMPLE_USER = row(
    id=1,
    email="user@bronic.ru",
    password_hash=_hash("Pass123"),
    role="user",
    name="Иван",
    surname="Иванов",
    created_at=datetime(2024, 1, 1),
    salt="somesalt",
)

SAMPLE_RESOURCE = row(
    id=42,
    name="Уютная студия",
    type="apartment",
    description="Хорошая квартира",
    base_price=3500.0,
    is_active=True,
    address="ул. Ленина, 1",
    location="Москва",
    image_url="img/property/studia.jpg",
)

SAMPLE_BOOKING = row(
    id=10,
    user_id=1,
    resource_id=42,
    start_time=datetime(2025, 7, 1),
    end_time=datetime(2025, 7, 7),
    status="CREATED",
    price=21000.0,
    created_at=datetime(2025, 3, 1),
    resource_name="Уютная студия",
    address="ул. Ленина, 1",
    location="Москва",
    image_url="img/property/studia.jpg",
)

SAMPLE_REVIEW = row(
    id=5,
    resource_id=42,
    author_name="Мария",
    rating=5,
    comment="Отлично!",
    created_at=datetime(2025, 4, 1),
)
