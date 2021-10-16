CREATE DATABASE kvmrepository

SET time_zone='+00:00';

CREATE TABLE keys (
    map_key VARCHAR(50) PRIMARY KEY,
    map_value VARCHAR(65535) COLLATE utf8mb4_general_ci
)

CREATE TABLE key_value_log (
    map_key VARCHAR(50) NOT NULL,
    map_value VARCHAR(65535) COLLATE utf8mb4_general_ci,
    ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    content_type INT NOT NULL DEFAULT 0,
    PRIMARY KEY(map_key, ts)
    FOREIGN KEY(map_key) REFERENCES keys(map_key)
)
