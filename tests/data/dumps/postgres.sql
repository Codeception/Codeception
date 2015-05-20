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
-- Name: empty_table; Type: TABLE; Schema: public; Owner: -; Tablespace:
--
DROP TABLE IF EXISTS empty_table CASCADE;
CREATE TABLE empty_table (
    id integer NOT NULL,
    field character varying
);


--
-- Name: empty_table_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE empty_table_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: empty_table_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE empty_table_id_seq OWNED BY empty_table.id;


--
-- Name: groups; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

DROP TABLE IF EXISTS groups CASCADE;
CREATE TABLE groups (
    name character varying(50),
    enabled boolean,
    created_at timestamp without time zone DEFAULT now(),
    id integer NOT NULL
);


--
-- Name: groups_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE groups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: groups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE groups_id_seq OWNED BY groups.id;


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

DROP TABLE IF EXISTS permissions CASCADE;
CREATE TABLE permissions (
    user_id integer,
    group_id integer,
    role character varying(10),
    id integer NOT NULL
);


--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE permissions_id_seq OWNED BY permissions.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

DROP TABLE IF EXISTS users CASCADE;
CREATE TABLE users (
    name character varying(30),
    email character varying(50),
    created_at timestamp without time zone DEFAULT now(),
    id integer NOT NULL
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY empty_table ALTER COLUMN id SET DEFAULT nextval('empty_table_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY groups ALTER COLUMN id SET DEFAULT nextval('groups_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY permissions ALTER COLUMN id SET DEFAULT nextval('permissions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- Data for Name: empty_table; Type: TABLE DATA; Schema: public; Owner: -
--

COPY empty_table (id, field) FROM stdin;
\.


--
-- Name: empty_table_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('empty_table_id_seq', 1, false);


--
-- Data for Name: groups; Type: TABLE DATA; Schema: public; Owner: -
--

COPY groups (name, enabled, created_at, id) FROM stdin;
coders	t	2012-02-02 22:33:30.807	1
jazzman	f	2012-02-02 22:33:35.271	2
\.


--
-- Name: groups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('groups_id_seq', 2, true);


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY permissions (user_id, group_id, role, id) FROM stdin;
1	1	member	1
2	1	member	2
3	2	member	9
4	2	admin	10
\.


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('permissions_id_seq', 10, true);


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY users (name, email, created_at, id) FROM stdin;
davert	davert@mail.ua	\N	1
nick	nick@mail.ua	2012-02-02 22:30:31.748	2
miles	miles@davis.com	2012-02-02 22:30:52.166	3
bird	charlie@parker.com	2012-02-02 22:32:13.107	4
\.


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('users_id_seq', 4, true);


--
-- Name: g1; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY groups
    ADD CONSTRAINT g1 PRIMARY KEY (id);


--
-- Name: p1; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT p1 PRIMARY KEY (id);


--
-- Name: u1; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY users
    ADD CONSTRAINT u1 PRIMARY KEY (id);


--
-- Name: pf1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT pf1 FOREIGN KEY (user_id) REFERENCES users(id);


--
-- Name: pg1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT pg1 FOREIGN KEY (group_id) REFERENCES groups(id);


--
-- start test for triggers with $$ syntax
--
INSERT INTO users (name, email) VALUES ('This $$ should work', 'user@example.org');
CREATE OR REPLACE FUNCTION upd_timestamp() RETURNS TRIGGER
LANGUAGE plpgsql
AS
$$
BEGIN
    NEW.created_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
  $$;

INSERT INTO users (name, email) VALUES ('This should work as well', 'user2@example.org');
--
-- end test for triggers with $$ syntax
--

--
-- PostgreSQL database dump complete
--

