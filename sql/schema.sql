-- CalorieVision schema.sql
SET NAMES utf8mb4;
SET foreign_key_checks = 0;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) UNIQUE NOT NULL,
    email VARCHAR(128) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    weight FLOAT DEFAULT NULL,
    goal_calories INT DEFAULT NULL,
    is_banned TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) UNIQUE NOT NULL,
    email VARCHAR(128) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_super TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (username, email, password, is_super)
VALUES ('admin', 'admin@demo.local', 
    -- The following hash = password: "adminpass123"
    '$2y$10$y8CYQFn0X1/mVptjTwr/pOHRfOChy31Tzm.QbGYvdrzJu.Z9xUpli', 1);

CREATE TABLE food_database (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    typical_portion_grams FLOAT NOT NULL,
    calories_per_100g FLOAT NOT NULL,
    protein_per_100g FLOAT NOT NULL,
    carb_per_100g FLOAT NOT NULL,
    fat_per_100g FLOAT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE meals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    detected_items JSON NOT NULL,
    calories FLOAT NOT NULL,
    macros JSON NOT NULL,
    confidence FLOAT,
    portion_grams FLOAT,
    suggestion TEXT,
    waste_score INT,
    is_leftover TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE meal_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meal_id INT NOT NULL,
    user_id INT NOT NULL,
    ab_group ENUM('A','B') NOT NULL,
    rating ENUM('accurate','inaccurate') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meal_id) REFERENCES meals(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE diary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    meal_id INT NOT NULL,
    meal_date DATE DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (meal_id) REFERENCES meals(id)
);

CREATE TABLE api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) UNIQUE NOT NULL,
    key VARCHAR(255) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    model VARCHAR(255) DEFAULT 'gemini-pro',
    enabled TINYINT(1) DEFAULT 1,
    ab_group ENUM('A','B') DEFAULT 'A',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    endpoint VARCHAR(128),
    request JSON,
    response JSON,
    status INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

SET foreign_key_checks = 1;
