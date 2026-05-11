-- 1. Create and Use Database
CREATE DATABASE IF NOT EXISTS rus_cab_db;
USE rus_cab_db;

-- 2. Users Table (Passengers - Integrated with is_active)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1, -- Moved from ALTER statement
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Drivers Table (Unified with metrics and wallet)
CREATE TABLE IF NOT EXISTS `drivers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `license_no` VARCHAR(50) NOT NULL,
  `vehicle_type` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `profile_photo` VARCHAR(255) DEFAULT 'default_avatar.png',
  `city` VARCHAR(100) DEFAULT 'Surat',
  `car_model` VARCHAR(100) DEFAULT 'Not Set',
  `car_color` VARCHAR(50) DEFAULT 'Not Set',
  `bank_account` VARCHAR(50) DEFAULT NULL,
  `wallet_balance` DECIMAL(10, 2) DEFAULT 0.00,
  `rating` DECIMAL(3,2) DEFAULT 5.00,
  
  -- Tracking Metrics Integrated
  `total_requests_received` INT DEFAULT 0,
  `total_requests_accepted` INT DEFAULT 0,
  `total_rides_cancelled` INT DEFAULT 0,
  `is_suspended` TINYINT(1) DEFAULT 0,
  
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `license_no` (`license_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Bookings Table (Unified with cancellation logic)
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `driver_id` INT(11) DEFAULT NULL,
    
    -- Location & Trip Details
    `pickup_location` VARCHAR(255) NOT NULL,
    `dropoff_location` VARCHAR(255) NOT NULL,
    `pickup_date` DATE NOT NULL,
    `pickup_time` TIME NOT NULL,
    `distance_km` DECIMAL(10, 2) DEFAULT 0.00,
    `fare` DECIMAL(10, 2) NOT NULL,
    `car_type` VARCHAR(50) DEFAULT 'Sedan',
    
    -- Ride State
    `status` ENUM('Pending', 'Accepted', 'Running', 'Completed', 'Cancelled') DEFAULT 'Pending',
    `otp` INT(4) NOT NULL,
    `is_rated` TINYINT(1) DEFAULT 0,
    
    -- Cancellation Details Integrated
    `cancel_reason` VARCHAR(255) DEFAULT NULL,
    `cancelled_by` ENUM('User', 'Driver') DEFAULT NULL,
    
    -- Payment Details
    `payment_method` ENUM('Cash', 'Card', 'UPI') DEFAULT 'Cash',
    `payment_status` ENUM('Unpaid', 'Paid') DEFAULT 'Unpaid',
    `razorpay_order_id` VARCHAR(255) DEFAULT NULL,
    `payment_id` VARCHAR(255) DEFAULT NULL,
    
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Ratings Table
CREATE TABLE IF NOT EXISTS `ratings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `booking_id` INT NOT NULL,
    `driver_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `stars` INT(1) CHECK (`stars` BETWEEN 1 AND 5),
    `feedback` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Fleet Table
CREATE TABLE IF NOT EXISTS `fleet` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `car_name` VARCHAR(100),
    `car_type` ENUM('Sedan', 'SUV', 'Luxury'),
    `rate_per_km` DECIMAL(10,2),
    `status` ENUM('Available', 'Maintenance') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Commission Settings Table (Moved into main creation block)
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `commission_rate` DECIMAL(5,2) DEFAULT 15.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Complaints / Support Tickets Table (Moved into main creation block)
CREATE TABLE IF NOT EXISTS `complaints` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT DEFAULT NULL,
    `driver_id` INT DEFAULT NULL,
    `booking_id` INT DEFAULT NULL,
    `subject` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('Open', 'Resolved') DEFAULT 'Open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 10. Mock Data Injection
-- --------------------------------------------------------
INSERT INTO `admins` (`username`, `email`, `password`) VALUES 
('Admin', 'admin@ruscab.com', 'admin123');

INSERT INTO `users` (`full_name`, `email`, `phone`, `password`) VALUES 
('Test User', 'user@ruscab.com', '9876543210', '123456');

INSERT INTO `drivers` (`first_name`, `last_name`, `email`, `phone`, `license_no`, `vehicle_type`, `password`, `car_model`, `car_color`) 
VALUES 
('Ramesh', 'Kumar', 'driver@ruscab.com', '8765432109', 'GJ-05-AB1234', 'Sedan', '123456', 'Suzuki Swift', 'White');

INSERT INTO `settings` (`commission_rate`) VALUES (15.00);