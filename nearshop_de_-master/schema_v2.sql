-- Database Schema Refactored for Phase 1

-- Users Table (Unified for Customers, Admins, Shop Owners, Delivery Partners)
-- Previously 'register_user'
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `f_name` varchar(255) NOT NULL,
  `l_name` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL UNIQUE, -- Used as Login ID
  `email_id` varchar(255) NOT NULL,
  `user_address` text NOT NULL,
  `user_password` varchar(255) NOT NULL, -- Plaintext currently (Legacy)
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('customer','super_admin','shop_owner','delivery_partner') NOT NULL DEFAULT 'customer',
  `status` enum('active','pending','suspended') NOT NULL DEFAULT 'active',
  `phone_no` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address_select` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart Table (Existing)
CREATE TABLE `cart_table` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `creat_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`cart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart Items (Existing)
CREATE TABLE `cart_items` (
  `cart_id` int(11) NOT NULL,
  `pr_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products (Existing)
CREATE TABLE `product` (
  `pr_id` int(11) NOT NULL AUTO_INCREMENT,
  `pr_name` varchar(255) NOT NULL,
  `pr_pr` decimal(10,2) NOT NULL, -- Price
  `pr_de` text NOT NULL, -- Description
  `pr_img_n` varchar(255) NOT NULL, -- Image Name
  `pr_cat` varchar(50) NOT NULL, -- Category
  `pr_qu` int(11) NOT NULL, -- Quantity
  `shop_id` int(11) DEFAULT NULL, -- Placeholder for Phase 3
  PRIMARY KEY (`pr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders (Existing, plan for Phase 7)
CREATE TABLE `order_table` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `order_date` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order Items (Existing)
CREATE TABLE `order_items` (
  `order_id` int(11) NOT NULL,
  `pr_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_pr` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Addresses (Existing)
CREATE TABLE `user_addresses` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `user_address` text NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin Page User (Deprecated/Migrated to users)
-- DROP TABLE `admin_page_user`; -- In trial_admin DB
