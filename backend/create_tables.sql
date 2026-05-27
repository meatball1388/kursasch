--
-- PostgreSQL database dump
--

\restrict Fyv4m3BPce6fVThrxdFqXdmRhBNb8nseRUDu0fBRkByC15rTXL3DlAcdAhbpkNd

-- Dumped from database version 18.3
-- Dumped by pg_dump version 18.3

-- Started on 2026-04-29 00:19:28

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 230 (class 1259 OID 25023)
-- Name: bookings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bookings (
    id integer NOT NULL,
    user_id integer,
    resource_id integer,
    start_time timestamp without time zone NOT NULL,
    end_time timestamp without time zone NOT NULL,
    status character varying(50) DEFAULT 'CREATED'::character varying,
    price numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.bookings OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 25022)
-- Name: bookings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bookings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bookings_id_seq OWNER TO postgres;

--
-- TOC entry 5083 (class 0 OID 0)
-- Dependencies: 229
-- Name: bookings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bookings_id_seq OWNED BY public.bookings.id;


--
-- TOC entry 222 (class 1259 OID 24948)
-- Name: messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.messages (
    id integer NOT NULL,
    user_id integer,
    resource_id integer,
    message text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    is_read boolean DEFAULT false
);


ALTER TABLE public.messages OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 24947)
-- Name: messages_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.messages_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.messages_id_seq OWNER TO postgres;

--
-- TOC entry 5084 (class 0 OID 0)
-- Dependencies: 221
-- Name: messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.messages_id_seq OWNED BY public.messages.id;


--
-- TOC entry 224 (class 1259 OID 24971)
-- Name: payments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payments (
    id integer NOT NULL,
    booking_id integer,
    amount numeric(10,2) NOT NULL,
    status character varying(50) DEFAULT 'PENDING'::character varying,
    payment_method character varying(50),
    external_id character varying(100),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.payments OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 24970)
-- Name: payments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.payments_id_seq OWNER TO postgres;

--
-- TOC entry 5085 (class 0 OID 0)
-- Dependencies: 223
-- Name: payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payments_id_seq OWNED BY public.payments.id;


--
-- TOC entry 228 (class 1259 OID 25009)
-- Name: resources; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.resources (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(50) NOT NULL,
    description text,
    base_price numeric(10,2) NOT NULL,
    is_active boolean DEFAULT true,
    address character varying(255),
    location character varying(255),
    image_url text
);


ALTER TABLE public.resources OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 25008)
-- Name: resources_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.resources_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.resources_id_seq OWNER TO postgres;

--
-- TOC entry 5086 (class 0 OID 0)
-- Dependencies: 227
-- Name: resources_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.resources_id_seq OWNED BY public.resources.id;


--
-- TOC entry 220 (class 1259 OID 24935)
-- Name: services; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.services (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    price numeric(10,2) NOT NULL,
    is_active boolean DEFAULT true
);


ALTER TABLE public.services OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 24934)
-- Name: services_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.services_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.services_id_seq OWNER TO postgres;

--
-- TOC entry 5087 (class 0 OID 0)
-- Dependencies: 219
-- Name: services_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.services_id_seq OWNED BY public.services.id;


--
-- TOC entry 226 (class 1259 OID 24994)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id integer NOT NULL,
    email character varying(255) NOT NULL,
    password_hash character varying(255) NOT NULL,
    name character varying(100),
    surname character varying(100),
    role character varying(50) DEFAULT 'user'::character varying,
    created_at date,
    salt text
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 24993)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- TOC entry 5088 (class 0 OID 0)
-- Dependencies: 225
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 4893 (class 2604 OID 25026)
-- Name: bookings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings ALTER COLUMN id SET DEFAULT nextval('public.bookings_id_seq'::regclass);


--
-- TOC entry 4883 (class 2604 OID 24951)
-- Name: messages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages ALTER COLUMN id SET DEFAULT nextval('public.messages_id_seq'::regclass);


--
-- TOC entry 4886 (class 2604 OID 24974)
-- Name: payments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments ALTER COLUMN id SET DEFAULT nextval('public.payments_id_seq'::regclass);


--
-- TOC entry 4891 (class 2604 OID 25012)
-- Name: resources id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resources ALTER COLUMN id SET DEFAULT nextval('public.resources_id_seq'::regclass);


--
-- TOC entry 4881 (class 2604 OID 24938)
-- Name: services id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.services ALTER COLUMN id SET DEFAULT nextval('public.services_id_seq'::regclass);


--
-- TOC entry 4889 (class 2604 OID 24997)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 5077 (class 0 OID 25023)
-- Dependencies: 230
-- Data for Name: bookings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bookings (id, user_id, resource_id, start_time, end_time, status, price, created_at) FROM stdin;
\.


--
-- TOC entry 5069 (class 0 OID 24948)
-- Dependencies: 222
-- Data for Name: messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.messages (id, user_id, resource_id, message, created_at, is_read) FROM stdin;
\.


--
-- TOC entry 5071 (class 0 OID 24971)
-- Dependencies: 224
-- Data for Name: payments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payments (id, booking_id, amount, status, payment_method, created_at) FROM stdin;
\.


--
-- TOC entry 5075 (class 0 OID 25009)
-- Dependencies: 228
-- Data for Name: resources; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.resources (id, name, type, description, base_price, is_active, address, location, image_url) FROM stdin;
1	Апартаменты «Metro Plus»	apartment	Уютная студия в центре города. Современный ремонт, вся необходимая техника, Wi-Fi. Рядом метро и остановки общественного транспорта.	2500.00	t	Москва, ул. Тверская, д. 15	Москва	../img/property/metro-plus.png
2	Загородный дом «Лесная сказка»	dacha	Просторный дом в лесу. Идеально для отдыха с семьёй или друзьями. Мангальная зона, баня, парковка.	4500.00	t	Московская обл., д. Лесное	Московская область	../img/property/lesnau-skazka.webp
3	Комната в квартире	room	Уютная комната в центре Москвы. Общая кухня и ванная. Отличный вариант для бюджетного проживания.	1200.00	t	Москва, ул. Arbat, д. 25	Москва	../img/property/komnata-arbat.jpg
4	Коттедж «VIP Luxury»	cottedzh	Роскошный коттедж с бассейном и сауной. Премиальный ремонт, панорамные окна, охраняемая территория.	8500.00	t	Московская обл., пос. Барвиха	Московская область	../img/property/kotedzh-luxery.webp
5	Студия «City Center»	apartment	Современная студия в деловом центре. Панорамные окна, вид на город. Подходит для командировок.	3200.00	t	Москва, Сити, Пресненская наб., д. 10	Москва	../img/property/studia.jpg
6	Дача «У озера»	dacha	Уютный домик на берегу озера. Рыбалка, прогулки на природе. Есть лодка и мангал.	3800.00	t	Московская обл., д. Озерки	Московская область	../img/property/dacha-u-ozera.jpg
8	Квартира по адресу Мск 	apartment	вавы	2500.00	t	Мск 	Мск	\N
\.


--
-- TOC entry 5067 (class 0 OID 24935)
-- Dependencies: 220
-- Data for Name: services; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.services (id, name, description, price, is_active) FROM stdin;
1	“Ў®аЄ 	”Ё­ «м­ п гЎ®аЄ  Ї®б«Ґ Їа®¦Ёў ­Ёп	1000.00	t
2	ђ ­­Ё© § Ґ§¤	‡ Ґ§¤ ¤® 12:00	500.00	t
3	Џ®§¤­Ё© ўлҐ§¤	‚лҐ§¤ Ї®б«Ґ 12:00	500.00	t
4	’а ­бдҐа	‚бваҐз /Їа®ў®¤л ­  ў®Є§ «Ґ Ё«Ё  на®Ї®авг	1500.00	t
5	Уборка	Финальная уборка после проживания	1000.00	t
6	Ранний заезд	Заезд до 12:00	500.00	t
7	Поздний выезд	Выезд после 12:00	500.00	t
8	Трансфер	Встреча/проводы на вокзале или аэропорту	1500.00	t
\.


--
-- TOC entry 5073 (class 0 OID 24994)
-- Dependencies: 226
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, email, password_hash, name, surname, role, created_at, salt) FROM stdin;
2	admin@example.com	$2b$12$K6uTtbQwIE.iLOqYgZR71OWUc1j0CsfgND0XroAQps/cJsSeWA4qO	Админ	Админов	admin	2026-04-18	$2b$12$LQv3c1yqBWVHxkd0LHAkCO
19	a@123	$2b$12$3CNrvoSI8TUgp68k0g/oTOpeqqRBYJKd12PZd9NDsqjZ0lmV35RbS	a	a	user	2026-04-29	$2b$12$3CNrvoSI8TUgp68k0g/oTO
\.


--
-- TOC entry 5089 (class 0 OID 0)
-- Dependencies: 229
-- Name: bookings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bookings_id_seq', 3, true);


--
-- TOC entry 5090 (class 0 OID 0)
-- Dependencies: 221
-- Name: messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.messages_id_seq', 1, false);


--
-- TOC entry 5091 (class 0 OID 0)
-- Dependencies: 223
-- Name: payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payments_id_seq', 1, false);


--
-- TOC entry 5092 (class 0 OID 0)
-- Dependencies: 227
-- Name: resources_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.resources_id_seq', 8, true);


--
-- TOC entry 5093 (class 0 OID 0)
-- Dependencies: 219
-- Name: services_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.services_id_seq', 8, true);


--
-- TOC entry 5094 (class 0 OID 0)
-- Dependencies: 225
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 19, true);


--
-- TOC entry 4913 (class 2606 OID 25034)
-- Name: bookings bookings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_pkey PRIMARY KEY (id);


--
-- TOC entry 4899 (class 2606 OID 24959)
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id);


--
-- TOC entry 4901 (class 2606 OID 24980)
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- TOC entry 4911 (class 2606 OID 25021)
-- Name: resources resources_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resources
    ADD CONSTRAINT resources_pkey PRIMARY KEY (id);


--
-- TOC entry 4897 (class 2606 OID 24946)
-- Name: services services_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.services
    ADD CONSTRAINT services_pkey PRIMARY KEY (id);


--
-- TOC entry 4904 (class 2606 OID 25007)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 4906 (class 2606 OID 25005)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 4914 (class 1259 OID 25048)
-- Name: idx_bookings_resource_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_bookings_resource_id ON public.bookings USING btree (resource_id);


--
-- TOC entry 4915 (class 1259 OID 25050)
-- Name: idx_bookings_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_bookings_status ON public.bookings USING btree (status);


--
-- TOC entry 4916 (class 1259 OID 25049)
-- Name: idx_bookings_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_bookings_user_id ON public.bookings USING btree (user_id);


--
-- TOC entry 4907 (class 1259 OID 25047)
-- Name: idx_resources_is_active; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_resources_is_active ON public.resources USING btree (is_active);


--
-- TOC entry 4908 (class 1259 OID 25045)
-- Name: idx_resources_location; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_resources_location ON public.resources USING btree (location);


--
-- TOC entry 4909 (class 1259 OID 25046)
-- Name: idx_resources_type; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_resources_type ON public.resources USING btree (type);


--
-- TOC entry 4902 (class 1259 OID 25051)
-- Name: idx_users_email; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_email ON public.users USING btree (email);


--
-- TOC entry 4917 (class 2606 OID 25040)
-- Name: bookings bookings_resource_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_resource_id_fkey FOREIGN KEY (resource_id) REFERENCES public.resources(id) ON DELETE CASCADE;


--
-- TOC entry 4918 (class 2606 OID 25035)
-- Name: bookings bookings_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


-- Completed on 2026-04-29 00:19:29

--
-- PostgreSQL database dump complete
--

\unrestrict Fyv4m3BPce6fVThrxdFqXdmRhBNb8nseRUDu0fBRkByC15rTXL3DlAcdAhbpkNd

