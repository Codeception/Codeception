--
-- PostgreSQL database dump
--

-- Started on 2012-02-03 00:00:32

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 1498 (class 1259 OID 32786)
-- Dependencies: 1783 3
-- Name: groups; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE groups (
    name character varying(50),
    created_at timestamp without time zone DEFAULT now(),
    id integer NOT NULL
);


--
-- TOC entry 1502 (class 1259 OID 32822)
-- Dependencies: 3 1498
-- Name: groups_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE groups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1801 (class 0 OID 0)
-- Dependencies: 1502
-- Name: groups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE groups_id_seq OWNED BY groups.id;


--
-- TOC entry 1802 (class 0 OID 0)
-- Dependencies: 1502
-- Name: groups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('groups_id_seq', 2, true);


--
-- TOC entry 1499 (class 1259 OID 32791)
-- Dependencies: 3
-- Name: permissions; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE permissions (
    user_id integer,
    group_id integer,
    role character varying(10),
    id integer NOT NULL
);


--
-- TOC entry 1501 (class 1259 OID 32812)
-- Dependencies: 1499 3
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1803 (class 0 OID 0)
-- Dependencies: 1501
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE permissions_id_seq OWNED BY permissions.id;


--
-- TOC entry 1804 (class 0 OID 0)
-- Dependencies: 1501
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('permissions_id_seq', 10, true);


--
-- TOC entry 1497 (class 1259 OID 32778)
-- Dependencies: 1781 3
-- Name: users; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE users (
    name character varying(30),
    email character varying(50),
    created_at timestamp without time zone DEFAULT now(),
    id integer NOT NULL
);


--
-- TOC entry 1500 (class 1259 OID 32806)
-- Dependencies: 1497 3
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1805 (class 0 OID 0)
-- Dependencies: 1500
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- TOC entry 1806 (class 0 OID 0)
-- Dependencies: 1500
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('users_id_seq', 4, true);


--
-- TOC entry 1782 (class 2604 OID 32824)
-- Dependencies: 1502 1498
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE groups ALTER COLUMN id SET DEFAULT nextval('groups_id_seq'::regclass);


--
-- TOC entry 1784 (class 2604 OID 32814)
-- Dependencies: 1501 1499
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE permissions ALTER COLUMN id SET DEFAULT nextval('permissions_id_seq'::regclass);


--
-- TOC entry 1780 (class 2604 OID 32808)
-- Dependencies: 1500 1497
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- TOC entry 1794 (class 0 OID 32786)
-- Dependencies: 1498
-- Data for Name: groups; Type: TABLE DATA; Schema: public; Owner: -
--

COPY groups (name, created_at, id) FROM stdin;
coders	2012-02-02 22:33:30.807	1
jazzman	2012-02-02 22:33:35.271	2
\.


--
-- TOC entry 1795 (class 0 OID 32791)
-- Dependencies: 1499
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY permissions (user_id, group_id, role, id) FROM stdin;
1	1	member	1
2	1	member	2
3	2	member	9
4	2	admin	10
\.


--
-- TOC entry 1793 (class 0 OID 32778)
-- Dependencies: 1497
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY users (name, email, created_at, id) FROM stdin;
davert	davert@mail.ua	\N	1
nick	nick@mail.ua	2012-02-02 22:30:31.748	2
miles	miles@davis.com	2012-02-02 22:30:52.166	3
bird	charlie@parker.com	2012-02-02 22:32:13.107	4
\.


--
-- TOC entry 1788 (class 2606 OID 32829)
-- Dependencies: 1498 1498
-- Name: g1; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY groups
    ADD CONSTRAINT g1 PRIMARY KEY (id);


--
-- TOC entry 1790 (class 2606 OID 32821)
-- Dependencies: 1499 1499
-- Name: p1; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT p1 PRIMARY KEY (id);


--
-- TOC entry 1786 (class 2606 OID 32819)
-- Dependencies: 1497 1497
-- Name: u1; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT u1 PRIMARY KEY (id);


--
-- TOC entry 1791 (class 2606 OID 32853)
-- Dependencies: 1497 1785 1499
-- Name: pf1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT pf1 FOREIGN KEY (user_id) REFERENCES users(id);


--
-- TOC entry 1792 (class 2606 OID 32858)
-- Dependencies: 1499 1498 1787
-- Name: pg1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT pg1 FOREIGN KEY (group_id) REFERENCES groups(id);


--
-- TOC entry 1800 (class 0 OID 0)
-- Dependencies: 3
-- Name: public; Type: ACL; Schema: -; Owner: -
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2012-02-03 00:00:32

--
-- PostgreSQL database dump complete
--

