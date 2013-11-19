DROP DATABASE phoenix;
CREATE DATABASE IF NOT EXISTS phoenix;
USE phoenix;

CREATE TABLE IF NOT EXISTS cities (
    id     INTEGER NOT  NULL AUTO_INCREMENT,
    name   VARCHAR(255) NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS couriers (
    id     INTEGER NOT  NULL AUTO_INCREMENT,
    name   VARCHAR(255) NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS senders (
    id       INTEGER NOT  NULL AUTO_INCREMENT,
    identity VARCHAR(255) NULL,
    name     VARCHAR(255) NULL,
    phone    VARCHAR(255) NULL,
    company  VARCHAR(255) NULL,
    address  VARCHAR(255) NULL,
    city     VARCHAR(255) NULL,
    state    VARCHAR(255) NULL,
    country  VARCHAR(255) NULL,
    postcode VARCHAR(255) NULL,
    backside_photo  VARCHAR(255) NULL,
    frontside_photo VARCHAR(255) NULL,
    signature_photo VARCHAR(255) NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS receivers (
    id       INTEGER NOT  NULL AUTO_INCREMENT,
    name     VARCHAR(255) NULL,
    phone    VARCHAR(255) NULL,
    company  VARCHAR(255) NULL,
    address  VARCHAR(255) NULL,
    city     VARCHAR(255) NULL,
    state    VARCHAR(255) NULL,
    country  VARCHAR(255) NULL,
    postcode VARCHAR(255) NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS packages (
    id              INTEGER NOT NULL AUTO_INCREMENT,
    original_weight VARCHAR(255) NULL,
    total_weight    VARCHAR(255) NULL,
    description     VARCHAR(255) NULL,
    quantity        VARCHAR(255) NULL,
    claim_value     VARCHAR(255) NULL,
    staff_signature VARCHAR(255) NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS waybills (
    id             INTEGER NOT NULL AUTO_INCREMENT,
    tracking_id    VARCHAR(255),
    sender_id      INTEGER      NULL,
    receiver_id    INTEGER      NULL,
    courier_id     INTEGER      NULL,
    package_id     INTEGER      NULL,
    city_id        INTEGER      NULL,
    order_date     VARCHAR(255) NULL,
    flight_date    VARCHAR(255) NULL,
    location       VARCHAR(255) NULL,
    postage        VARCHAR(255) NULL,
    insurance      VARCHAR(255) NULL,
    tax            VARCHAR(255) NULL,
    packing_charge VARCHAR(255) NULL,
    total_price    VARCHAR(255) NULL,
    agent_number   VARCHAR(255) NULL,
    agent_price    VARCHAR(255) NULL,
    note           VARCHAR(255) NULL,
    express_number VARCHAR(255) NULL,
    waybill_status VARCHAR(255) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (sender_id) REFERENCES senders(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (courier_id) REFERENCES couriers(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES receivers(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (city_id) REFERENCES cities(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS statuses (
    id INTEGER NOT NULL AUTO_INCREMENT,
    waybill_id INTEGER NULL,
    time       VARCHAR(255) NULL,
    context    VARCHAR(255) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (waybill_id) REFERENCES waybills(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS logs (
    id INTEGER NOT NULL AUTO_INCREMENT,
    date_created      VARCHAR(255),
    number_of_waybill INTEGER,
    scope             VARCHAR(255),
    location          VARCHAR(255),
    download_link     VARCHAR(255),
    PRIMARY KEY (id)
);

INSERT INTO couriers(name) VALUES('huitongkuaidi');
INSERT INTO couriers(name) VALUES('shentong');
INSERT INTO couriers(name) VALUES('ems');
INSERT INTO couriers(name) VALUES('shunfeng');
INSERT INTO couriers(name) VALUES('yuantong');
INSERT INTO couriers(name) VALUES('yunda');

INSERT INTO cities(name) VALUES('HK');
INSERT INTO cities(name) VALUES('SH');
INSERT INTO cities(name) VALUES('CQ');
INSERT INTO cities(name) VALUES('TJ');
