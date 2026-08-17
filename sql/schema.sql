-- One More Game: minimal schema needed for Browse Page (B.1)
-- Run this in phpMyAdmin or via the MySQL CLI against your onemoregame_db database

CREATE DATABASE IF NOT EXISTS onemoregame_db;
USE onemoregame_db;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    skill_level ENUM('Beginner','Intermediate','Competitive') DEFAULT 'Beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS courts (
    court_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150) NOT NULL,
    status ENUM('open','partial','full') DEFAULT 'open'
);

CREATE TABLE IF NOT EXISTS games (
    game_id INT AUTO_INCREMENT PRIMARY KEY,
    host_id INT NOT NULL,
    court_id INT NOT NULL,
    format ENUM('5v5','4v4','3v3','2v2') NOT NULL,
    start_time DATETIME NOT NULL,
    max_players INT NOT NULL,
    skill_level ENUM('Beginner','Intermediate','Competitive') NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    status ENUM('active','cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (host_id) REFERENCES users(user_id),
    FOREIGN KEY (court_id) REFERENCES courts(court_id)
);

CREATE TABLE IF NOT EXISTS roster (
    roster_id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(game_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE KEY unique_join (game_id, user_id) -- prevents joining the same game twice
);

-- Sample seed data so the Browse page has something to display right away
INSERT INTO courts (name, location, status) VALUES
    ('Court 1', 'Rec Center - Main Gym', 'open'),
    ('Court 2', 'Outdoor - West Lot', 'partial'),
    ('Court 3', 'Rec Center - Annex', 'full');

INSERT INTO users (name, email, password_hash, skill_level) VALUES
    ('Jordan Smith', 'jordan@students.rowan.edu', '$2y$10$examplehashvalue', 'Intermediate'),
    ('Priya Patel', 'priya@students.rowan.edu', '$2y$10$examplehashvalue', 'Beginner');

INSERT INTO games (host_id, court_id, format, start_time, max_players, skill_level, description) VALUES
    (1, 1, '5v5', NOW() + INTERVAL 2 HOUR, 10, 'Intermediate', 'Bring a light and dark shirt'),
    (2, 2, '3v3', NOW() + INTERVAL 3 HOUR, 6, 'Beginner', 'Women''s game, open to all skill levels');
