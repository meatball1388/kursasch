
-- Add passport column to bookings table
ALTER TABLE public.bookings ADD COLUMN IF NOT EXISTS passport character varying(20);
ALTER TABLE public.bookings ADD COLUMN IF NOT EXISTS comment text;

-- Check if they exist (just to be safe)
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='bookings' AND column_name='passport') THEN
        ALTER TABLE public.bookings ADD COLUMN passport character varying(20);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='bookings' AND column_name='comment') THEN
        ALTER TABLE public.bookings ADD COLUMN comment text;
    END IF;
END $$;
