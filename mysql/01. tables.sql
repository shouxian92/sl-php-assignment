SET time_zone='+00:00';

CREATE TABLE objects (
    obj_key VARCHAR(50) PRIMARY KEY,
    obj_value TEXT
);

CREATE TABLE objects_log (
    obj_key VARCHAR(50) NOT NULL,
    obj_value TEXT,
    ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    content_type INT NOT NULL DEFAULT 0,
    PRIMARY KEY(obj_key, ts),
    FOREIGN KEY(obj_key) REFERENCES objects(obj_key)
);
