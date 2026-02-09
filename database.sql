-- Créer la base de données ICE DOG
CREATE DATABASE IF NOT EXISTS icedog;
USE icedog;

-- Table UTILISATEURS
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(120) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table CONTACTS (Messages de contact)
CREATE TABLE IF NOT EXISTS contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(120) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contact_email (email)
);

-- Table ABONNEMENTS
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    subscription_type ENUM('monthly', 'quarterly', 'annual') NOT NULL,
    dog_name VARCHAR(255) NOT NULL,
    dog_breed VARCHAR(150),
    dog_age INT,
    dog_weight DECIMAL(6,2),
    start_date DATE NOT NULL,
    end_date DATE,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'paused', 'cancelled') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table RÉSERVATIONS
CREATE TABLE IF NOT EXISTS reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    subscription_id INT,
    dog_name VARCHAR(255) NOT NULL,
    dog_breed VARCHAR(150),
    dog_age INT,
    dog_weight DECIMAL(6,2),
    service_type ENUM('forfait', 'abonnement') NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    duration INT DEFAULT 120,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('airtel_money', 'moov_money', 'cash') DEFAULT 'cash',
    payment_status ENUM('unpaid', 'paid', 'pending') DEFAULT 'unpaid',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL
);

-- Table PAIEMENTS
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reservation_id INT,
    user_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('airtel_money', 'moov_money', 'cash') NOT NULL,
    transaction_id VARCHAR(255),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    paid_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insérer un utilisateur de démonstration
INSERT INTO users (email, password, name, phone) 
VALUES ('demo@icedog.com', SHA2('demo123', 256), 'Utilisateur Demo', '065778010');

-- Créer des indices pour optimiser les recherches
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_reservation_user ON reservations(user_id);
CREATE INDEX idx_reservation_date ON reservations(appointment_date);
CREATE INDEX idx_subscription_user ON subscriptions(user_id);
CREATE INDEX idx_payment_user ON payments(user_id);
