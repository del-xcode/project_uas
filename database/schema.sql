CREATE DATABASE IF NOT EXISTS carwash_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE carwash_management;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  service_name VARCHAR(100) NOT NULL,
  description TEXT,
  price DECIMAL(12,2) NOT NULL,
  duration INT NOT NULL,
  status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
);

CREATE TABLE vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  vehicle_type VARCHAR(50) NOT NULL,
  brand VARCHAR(100) NOT NULL,
  plate_number VARCHAR(20) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  vehicle_id INT NOT NULL,
  service_id INT NOT NULL,
  booking_date DATE NOT NULL,
  booking_time TIME NOT NULL,
  status ENUM('pending', 'process', 'done', 'cancelled') NOT NULL DEFAULT 'pending',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  transaction_id VARCHAR(100) NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  payment_status ENUM('pending', 'paid', 'failed', 'expired') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, phone, password, role) VALUES
('Admin', 'admin@carwash.test', '08123456789', '$2y$10$NyrPMILMPy6XXbyhg0MqC.oqEqHwKRHkZyeJUcXIAcqhTZl3Nkv0i', 'admin');

INSERT INTO services (service_name, description, price, duration, status) VALUES
('Cuci Motor', 'Layanan cuci motor standar dan cepat.', 15000, 30, 'active'),
('Cuci Mobil', 'Layanan cuci mobil luar dalam.', 35000, 45, 'active'),
('Salon Kendaraan', 'Perawatan detail untuk interior dan eksterior.', 75000, 90, 'active');