CREATE TABLE url
(
    id INT(11) PRIMARY KEY NOT NULL,
    url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
