-- Database: rental_motor

CREATE DATABASE IF NOT EXISTS rental_motor;
USE rental_motor;

-- Table: admins
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Super Admin', 'Admin') DEFAULT 'Admin',
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone_number VARCHAR(20),
    otp_code VARCHAR(6),
    otp_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: motors
CREATE TABLE IF NOT EXISTS motors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    plate_number VARCHAR(20) NOT NULL UNIQUE,
    color VARCHAR(30) NOT NULL,
    description TEXT,
    price_per_day DECIMAL(10, 2) NOT NULL,
    stok INT DEFAULT 1,
    status ENUM('Tersedia', 'Disewa', 'Maintenance') DEFAULT 'Tersedia',
    is_popular BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: motor_images
CREATE TABLE IF NOT EXISTS motor_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    motor_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (motor_id) REFERENCES motors(id) ON DELETE CASCADE
);

-- Table: bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    motor_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    duration INT NOT NULL,
    location TEXT,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Disewa', 'Selesai', 'Batal') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (motor_id) REFERENCES motors(id)
);

-- Table: testimonials
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    message TEXT NOT NULL,
    photo VARCHAR(255),
    is_displayed BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: promos
CREATE TABLE IF NOT EXISTS promos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    discount_type ENUM('Percent', 'Nominal') NOT NULL,
    discount_value DECIMAL(10, 2) NOT NULL,
    valid_from DATE NOT NULL,
    valid_to DATE NOT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: settings
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
);

-- Table: activity_logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);

-- Default Admin
INSERT INTO admins (username, password, role, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'Main Administrator');
-- Password is 'password'

-- Initial Settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('site_name', 'JS Rental'),
('whatsapp_number', '6281214025894'),
('address', 'Jl. Malioboro No. 123, Yogyakarta 55271'),
('email', 'email_motorjoaja@gmail.com'),
('operational_hours', '08:00 - 22:00 WIB'),
('late_fee_per_day', '10000'),
('delivery_fee', '0'),
('meta_keywords', 'sewa motor jogja, rental motor yogyakarta, sewa motor murah'),
('meta_description', 'Penyedia layanan sewa motor terbaik di Yogyakarta dengan harga terjangkau dan pelayanan terpercaya.');
