CREATE TABLE key_value_log (
    map_key VARCHAR(50) NOT NULL
    date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    content_type INT NOT NULL DEFAULT 0
    PRIMARY KEY(map_key, date_time)
)

SET time_zone='+00:00';