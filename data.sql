CREATE DATABASE IF NOT EXISTS postal;
USE postal;

CREATE TABLE IF NOT EXISTS users (
    id                INTEGER NOT NULL AUTO_INCREMENT,
    profile_image     VARCHAR(200) NOT NULL,
    profile_thumbnail VARCHAR(200) NOT NULL,
    PRIMARY KEY (id)
);

