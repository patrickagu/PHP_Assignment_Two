use Patrick200626972;

-- Table for storing user accounts
CREATE TABLE `shop_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

select * from users_list;
select * from products;

-- Create users_list table
CREATE TABLE users_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    stock_quantity INT DEFAULT 0,
    image_path VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users_list(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create an initial admin user
-- Default password: Admin123 (will be hashed when stored)
INSERT INTO users_list (name, email, password, role) 
VALUES ('Admin User', 'admin@grabandgo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Create sample products
INSERT INTO products (name, description, price, category, stock_quantity)
VALUES 
    ('Fresh Apples', 'Crisp and juicy red apples', 2.99, 'Fruits', 100),
    ('Whole Wheat Bread', 'Freshly baked whole wheat loaf', 3.49, 'Bakery', 50),
    ('Organic Milk', '1 gallon of organic whole milk', 4.99, 'Dairy', 30),
    ('Free Range Eggs', 'Dozen large free range eggs', 3.99, 'Dairy', 40),
    ('Ground Beef', '1 lb of premium ground beef', 5.99, 'Meat', 25);
    
INSERT INTO products (name, description, price, category, stock_quantity)
VALUES 
	('Canadian Chicken', '3 lb of fresh Canadian chicken', 4.49, 'Meat', 60);
    
ALTER TABLE users_list 
MODIFY COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user';

select * from products;

ALTER TABLE users_list ADD COLUMN is_admin BOOLEAN DEFAULT FALSE;
    
ALTER TABLE users_list
ADD COLUMN phone VARCHAR(20) DEFAULT NULL,
ADD COLUMN address TEXT DEFAULT NULL,
ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL,
ADD COLUMN location VARCHAR(100) DEFAULT NULL;

ALTER TABLE users_list 
ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL COMMENT 'Stores filename of profile picture';

UPDATE users_list SET is_admin = 0 WHERE id = 2; 
UPDATE users_list SET role = user WHERE id = 2;

CREATE TABLE requested_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users_list(id)
);