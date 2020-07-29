CREATE SCHEMA "Broker";

CREATE TABLE "Broker"."request"
(
    "id"      VARCHAR(15)
        CONSTRAINT "request_pk"
            PRIMARY KEY,
    "message" VARCHAR(50)
);

