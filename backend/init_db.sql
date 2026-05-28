-- ============================================================
-- Инициализация БД для Docker (чистая версия без pg_dump метаданных)
-- ============================================================

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;

-- ──────────────────────── ТАБЛИЦЫ ────────────────────────

CREATE TABLE IF NOT EXISTS public.services (
    id SERIAL PRIMARY KEY,
    name  VARCHAR(255) NOT NULL,
    description TEXT,
    price NUMERIC(10,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS public.users (
    id SERIAL PRIMARY KEY,
    email    VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name     VARCHAR(100),
    surname  VARCHAR(100),
    role     VARCHAR(50) DEFAULT 'user',
    created_at DATE,
    salt     TEXT
);

CREATE TABLE IF NOT EXISTS public.resources (
    id SERIAL PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    type        VARCHAR(50)  NOT NULL,
    description TEXT,
    base_price  NUMERIC(10,2) NOT NULL,
    is_active   BOOLEAN DEFAULT TRUE,
    address     VARCHAR(255),
    location    VARCHAR(255),
    image_url   TEXT,
    area        INTEGER DEFAULT 0,
    guests      INTEGER DEFAULT 0,
    bedrooms    INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS public.messages (
    id SERIAL PRIMARY KEY,
    user_id     INTEGER REFERENCES public.users(id) ON DELETE CASCADE,
    resource_id INTEGER REFERENCES public.resources(id) ON DELETE CASCADE,
    message     TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read     BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS public.payments (
    id SERIAL PRIMARY KEY,
    booking_id     INTEGER,
    amount         NUMERIC(10,2) NOT NULL,
    status         VARCHAR(50) DEFAULT 'PENDING',
    payment_method VARCHAR(50),
    external_id    VARCHAR(100),
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.bookings (
    id SERIAL PRIMARY KEY,
    user_id     INTEGER REFERENCES public.users(id) ON DELETE CASCADE,
    resource_id INTEGER REFERENCES public.resources(id) ON DELETE CASCADE,
    start_time  TIMESTAMP NOT NULL,
    end_time    TIMESTAMP NOT NULL,
    status      VARCHAR(50) DEFAULT 'CREATED',
    price       NUMERIC(10,2) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ──────────────────────── ИНДЕКСЫ ────────────────────────

CREATE INDEX IF NOT EXISTS idx_resources_location   ON public.resources(location);
CREATE INDEX IF NOT EXISTS idx_resources_type       ON public.resources(type);
CREATE INDEX IF NOT EXISTS idx_resources_is_active  ON public.resources(is_active);
CREATE INDEX IF NOT EXISTS idx_bookings_user_id     ON public.bookings(user_id);
CREATE INDEX IF NOT EXISTS idx_bookings_resource_id ON public.bookings(resource_id);
CREATE INDEX IF NOT EXISTS idx_bookings_status      ON public.bookings(status);
CREATE INDEX IF NOT EXISTS idx_users_email          ON public.users(email);

-- ──────────────────────── НАЧАЛЬНЫЕ ДАННЫЕ ────────────────────────

-- Сервисы
INSERT INTO public.services (name, description, price, is_active) VALUES
('Уборка',         'Финальная уборка после проживания', 1000.00, TRUE),
('Ранний заезд',   'Заезд до 12:00',                    500.00,  TRUE),
('Поздний выезд',  'Выезд после 12:00',                 500.00,  TRUE),
('Трансфер',       'Встреча/проводы на вокзале или аэропорту', 1500.00, TRUE)
ON CONFLICT DO NOTHING;

-- Администратор (пароль: admin)
INSERT INTO public.users (email, password_hash, name, surname, role, created_at, salt) VALUES
('admin@example.com',
 '$2b$12$K6uTtbQwIE.iLOqYgZR71OWUc1j0CsfgND0XroAQps/cJsSeWA4qO',
 'Админ', 'Админов', 'admin', '2026-04-18',
 '$2b$12$LQv3c1yqBWVHxkd0LHAkCO')
ON CONFLICT (email) DO NOTHING;

-- Объекты недвижимости
INSERT INTO public.resources (name, type, description, base_price, is_active, address, location, image_url, area, guests, bedrooms) VALUES
('Апартаменты «Metro Plus»',    'apartment',
 'Уютная студия в центре города. Современный ремонт, вся необходимая техника, Wi-Fi. Рядом метро.',
 2500.00, TRUE, 'Москва, ул. Тверская, д. 15', 'Москва',
 '../img/property/metro-plus.png', 45, 2, 1),

('Загородный дом «Лесная сказка»', 'dacha',
 'Просторный дом в лесу. Идеально для отдыха с семьёй. Мангальная зона, баня, парковка.',
 4500.00, TRUE, 'Московская обл., д. Лесное', 'Московская область',
 '../img/property/lesnau-skazka.webp', 120, 6, 3),

('Комната в квартире', 'room',
 'Уютная комната в центре Москвы. Общая кухня и ванная. Отличный вариант для бюджетного проживания.',
 1200.00, TRUE, 'Москва, ул. Арбат, д. 25', 'Москва',
 '../img/property/komnata-arbat.jpg', 15, 1, 1),

('Коттедж «VIP Luxury»', 'cottedzh',
 'Роскошный коттедж с бассейном и сауной. Премиальный ремонт, панорамные окна.',
 8500.00, TRUE, 'Московская обл., пос. Барвиха', 'Московская область',
 '../img/property/kotedzh-luxery.webp', 250, 8, 4),

('Студия «City Center»', 'apartment',
 'Современная студия в деловом центре. Панорамные окна, вид на город. Подходит для командировок.',
 3200.00, TRUE, 'Москва, Сити, Пресненская наб., д. 10', 'Москва',
 '../img/property/studia.jpg', 38, 2, 1),

('Дача «У озера»', 'dacha',
 'Уютный домик на берегу озера. Рыбалка, прогулки на природе. Есть лодка и мангал.',
 3800.00, TRUE, 'Московская обл., д. Озерки', 'Московская область',
 '../img/property/dacha-u-ozera.jpg', 65, 4, 2)
ON CONFLICT DO NOTHING;
