-- Adminer 4.7.6 PostgreSQL dump

DROP TABLE IF EXISTS "auth_tokens";
DROP SEQUENCE IF EXISTS auth_tokens_id_seq;
CREATE SEQUENCE auth_tokens_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 START 1 CACHE 1;

CREATE TABLE "public"."auth_tokens" (
    "id" integer DEFAULT nextval('auth_tokens_id_seq') NOT NULL,
    "selector" text NOT NULL,
    "token" text NOT NULL,
    "userid" text NOT NULL,
    "expires" timestamp NOT NULL
) WITH (oids = false);


DROP TABLE IF EXISTS "users";
DROP SEQUENCE IF EXISTS users_id_seq;
CREATE SEQUENCE users_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 START 1 CACHE 1;

CREATE TABLE "public"."users" (
    "id" integer DEFAULT nextval('users_id_seq') NOT NULL,
    "email" text NOT NULL,
    "password" text NOT NULL,
    "text" text NOT NULL
) WITH (oids = false);


-- 2020-08-18 22:17:00.989241+00
