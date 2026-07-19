-- ============================================================
-- Online Bookstore - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS `online_bookstore` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `online_bookstore`;

-- ------------------------------------------------------------
-- Table: roles
-- ------------------------------------------------------------
CREATE TABLE `roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: users
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `profile_pic` VARCHAR(255) DEFAULT 'default.png',
  `role_id` INT NOT NULL DEFAULT 2,
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_token_expiry` DATETIME DEFAULT NULL,
  `otp_code` VARCHAR(6) DEFAULT NULL,
  `otp_expiry` DATETIME DEFAULT NULL,
  `is_verified` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_role_id` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: categories
-- ------------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_category_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: books
-- ------------------------------------------------------------
CREATE TABLE `books` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(255) NOT NULL,
  `isbn` VARCHAR(20) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `discount` DECIMAL(5,2) DEFAULT 0.00,
  `rating` DECIMAL(3,2) DEFAULT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `image` VARCHAR(255) DEFAULT 'default-book.png',
  `category_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_title` (`title`),
  KEY `idx_author` (`author`),
  KEY `idx_price` (`price`),
  CONSTRAINT `fk_books_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: cart
-- ------------------------------------------------------------
CREATE TABLE `cart` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `book_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_book_id` (`book_id`),
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_book` FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: wishlist
-- ------------------------------------------------------------
CREATE TABLE `wishlist` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `book_id` INT NOT NULL,
  `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_book` (`user_id`, `book_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_book_id` (`book_id`),
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wishlist_book` FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: orders
-- ------------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` TEXT NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'cod',
  `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_order_date` (`order_date`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: order_items
-- ------------------------------------------------------------
CREATE TABLE `order_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `book_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_book_id` (`book_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_book` FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Seed Data
-- ------------------------------------------------------------
INSERT INTO `roles` (`role_name`) VALUES ('admin'), ('user');

-- Default admin: admin@bookstore.com / Admin@123
INSERT INTO `users` (`username`, `email`, `password`, `role_id`, `is_verified`) VALUES
('Administrator', 'admin@bookstore.com', '$2y$10$wkEMMZimCMmklSPauvnlvuakys9Or6ESiPWVijAAu5ro2QyN8/RAq', 1, 1);

INSERT INTO `categories` (`name`, `description`) VALUES
('Fiction', 'Fictional books and novels'),
('Non-Fiction', 'Educational and informational books'),
('Science', 'Science and technology books'),
('History', 'Historical books'),
('Fantasy', 'Fantasy and adventure books'),
('Biography', 'Biographies and memoirs');

INSERT INTO `books` (`title`, `author`, `isbn`, `description`, `price`, `discount`, `rating`, `stock`, `image`, `category_id`) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0-7432-7356-5', 'A story of the mysteriously wealthy Jay Gatsby and his obsessive love for Daisy Buchanan, set in the Roaring Twenties.', 549, 29.00, 4.5, 50, '9780743273565.jpg', 1),
('To Kill a Mockingbird', 'Harper Lee', '978-0-06-112008-4', 'A novel about racial injustice in the Deep South, seen through the eyes of young Scout Finch.', 649, 15.00, 4.8, 40, '9780061120084.jpg', 1),
('1984', 'George Orwell', '978-0-452-28423-4', 'A dystopian social science fiction novel set in a totalitarian society ruled by Big Brother.', 399, 20.00, 4.6, 60, '9780452284234.jpg', 1),
('A Brief History of Time', 'Stephen Hawking', '978-0-553-38016-3', 'A landmark volume in science writing exploring black holes, the Big Bang, and the nature of time.', 899, 10.00, 4.3, 30, '9780553380163.jpg', 3),
('Sapiens', 'Yuval Noah Harari', '978-0-06-231609-7', 'A brief history of humankind from the Stone Age to the modern technological era.', 749, NULL, 4.7, 45, '9780062316097.jpg', 2),
('The Hobbit', 'J.R.R. Tolkien', '978-0-547-92822-7', 'A fantasy novel about Bilbo Baggins on an epic quest to reclaim the lost Dwarf Kingdom of Erebor.', 499, 25.00, 4.9, 55, '9780547928227.jpg', 5),
('Steve Jobs', 'Walter Isaacson', '978-1-4516-4853-9', 'The biography of Steve Jobs based on more than forty interviews with Jobs over two years.', 599, NULL, 4.4, 35, '9781451648539.jpg', 6),
('The Art of War', 'Sun Tzu', '978-1-59030-225-9', 'An ancient Chinese military treatise that has become a classic of strategy and philosophy.', 299, 30.00, 4.2, 70, '9781590302259.jpg', 2);
