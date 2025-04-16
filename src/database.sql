-- Step 1: Create the database
CREATE DATABASE party_planner CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Step 2: Use the database
USE party_planner;

-- Step 3: Create base tables (no foreign keys)
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(30) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_admin TINYINT(1) DEFAULT 0,
    is_active INT(11) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);

CREATE TABLE hotels (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    country VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    image_path VARCHAR(255),
    amount_per_night DECIMAL(10,2) NOT NULL,
    capacity INT(11) NOT NULL,
    min_booking_days INT(11) DEFAULT 1,
    max_booking_days INT(11) DEFAULT 365,
    rating_score DECIMAL(3,2) DEFAULT 0.00,
    reviews INT(11) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE TABLE food_items (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    cost_per_head DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE additional_items (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    cost_per_item DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE contact_messages (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    PRIMARY KEY (id)
);

-- Step 4: Create tables with foreign keys to existing tables
CREATE TABLE hotel_amenities (
    id INT(11) NOT NULL AUTO_INCREMENT,
    hotel_id INT(11) NOT NULL,
    amenity VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);

CREATE TABLE bookings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    hotel_id INT(11) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    no_of_persons INT(11) NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','cancelled','completed','rejected') DEFAULT 'confirmed',
    admin_notes TEXT DEFAULT 'This is confirmed',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);

CREATE TABLE booking_food_items (
    id INT(11) NOT NULL AUTO_INCREMENT,
    booking_id INT(11) NOT NULL,
    food_item_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE CASCADE
);
