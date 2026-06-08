
-- ============================================================
-- МИГРАЦИЯ: НОРМАЛИЗАЦИЯ БД
-- ============================================================

-- 1. Удобства (Amenities)
CREATE TABLE IF NOT EXISTS public.amenities (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS public.resource_amenities (
    resource_id INTEGER REFERENCES public.resources(id) ON DELETE CASCADE,
    amenity_id INTEGER REFERENCES public.amenities(id) ON DELETE CASCADE,
    PRIMARY KEY (resource_id, amenity_id)
);

-- 2. Гостевые профили (Guest Profiles) - 1:1 к бронированию
CREATE TABLE IF NOT EXISTS public.guest_profiles (
    id SERIAL PRIMARY KEY,
    booking_id INTEGER REFERENCES public.bookings(id) ON DELETE CASCADE UNIQUE,
    name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    passport TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Логи аудита (Audit Logs)
CREATE TABLE IF NOT EXISTS public.audit_logs (
    id SERIAL PRIMARY KEY,
    action VARCHAR(255) NOT NULL,
    user_id INTEGER REFERENCES public.users(id) ON DELETE SET NULL,
    resource_id INTEGER REFERENCES public.resources(id) ON DELETE SET NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Привязка отзывов к пользователям
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='reviews' AND column_name='user_id') THEN
        ALTER TABLE public.reviews ADD COLUMN user_id INTEGER REFERENCES public.users(id) ON DELETE SET NULL;
    END IF;
END $$;

-- 5. Базовые удобства для затравки
INSERT INTO public.amenities (name, icon) VALUES
('Wi-Fi', 'bi-wifi'),
('Парковка', 'bi-p-circle'),
('Кухня', 'bi-egg-fried'),
('Кондиционер', 'bi-snow'),
('ТВ', 'bi-tv'),
('Сейф', 'bi-safe'),
('Бассейн', 'bi-water'),
('Баня', 'bi-thermometer-half'),
('Мангал', 'bi-fire'),
('Рабочее место', 'bi-laptop')
ON CONFLICT (name) DO NOTHING;
