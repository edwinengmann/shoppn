-- Fresh-install schema only. Select an empty database before importing.
-- Includes catalog data; customer, cart, and payment records are omitted.
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(100) NOT NULL,
  `brand_cat` int(11) DEFAULT NULL,
  `brand_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`brand_id`),
  KEY `brand_cat` (`brand_cat`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(100) NOT NULL,
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `brands`
  ADD CONSTRAINT `brands_ibfk_1`
  FOREIGN KEY (`brand_cat`) REFERENCES `categories` (`cat_id`);

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(50) NOT NULL,
  `customer_pass` varchar(150) NOT NULL,
  `customer_country` varchar(30) NOT NULL,
  `customer_city` varchar(30) NOT NULL,
  `customer_contact` varchar(15) NOT NULL,
  `customer_address` varchar(255) NOT NULL DEFAULT '',
  `customer_image` varchar(100) DEFAULT NULL,
  `user_role` int(11) NOT NULL DEFAULT 2,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `customer_email` (`customer_email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_cat` int(11) NOT NULL,
  `product_brand` int(11) NOT NULL,
  `product_title` varchar(200) NOT NULL,
  `product_price` double NOT NULL,
  `product_desc` varchar(500) DEFAULT NULL,
  `product_image` varchar(100) DEFAULT NULL,
  `product_keywords` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `product_cat` (`product_cat`),
  KEY `product_brand` (`product_brand`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`product_cat`) REFERENCES `categories` (`cat_id`),
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`product_brand`) REFERENCES `brands` (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `p_id` int(11) NOT NULL,
  `ip_add` varchar(50) NOT NULL,
  `c_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `size` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`cart_id`),
  KEY `p_id` (`p_id`),
  KEY `c_id` (`c_id`),
  UNIQUE KEY `cart_customer_product_size` (`c_id`, `p_id`, `size`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`p_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`c_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `invoice_no` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `order_status` varchar(100) NOT NULL DEFAULT 'paid',
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `orderdetails` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `payment` (
  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
  `amt` double NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `currency` text NOT NULL,
  `payment_date` date NOT NULL,
  PRIMARY KEY (`pay_id`),
  KEY `customer_id` (`customer_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Paystack orders and payments (consolidated from paystack_orders_migration.sql).
CREATE TABLE `shop_orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_contact` varchar(30) NOT NULL,
  `delivery_address` varchar(255) NOT NULL DEFAULT '',
  `delivery_city` varchar(100) NOT NULL DEFAULT '',
  `delivery_country` varchar(100) NOT NULL DEFAULT '',
  `amount` decimal(12,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'GHS',
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `shop_orders_customer_id` (`customer_id`),
  KEY `shop_orders_status` (`status`),
  CONSTRAINT `shop_orders_customer_fk`
    FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shop_order_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_title` varchar(200) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`item_id`),
  KEY `shop_order_items_order_id` (`order_id`),
  CONSTRAINT `shop_order_items_order_fk`
    FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`order_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shop_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `reference` varchar(100) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'GHS',
  `offered_channels` varchar(255) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(40) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `shop_payments_reference` (`reference`),
  KEY `shop_payments_order_id` (`order_id`),
  CONSTRAINT `shop_payments_order_fk`
    FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`order_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Public catalog seed data from the supplied dump.
-- Customer, cart, and payment rows are intentionally omitted.
INSERT INTO `categories` (`cat_id`, `cat_name`) VALUES
(1, 'Clothing'),
(2, 'Tech'),
(4, 'Footwear');

INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_cat`, `brand_image`) VALUES
(1, 'Cernucci', 1, 'images/brands/4ebc0fca0c901a0fed26c42f95dbfa44.webp'),
(2, 'Apple', 2, 'images/brands/577525d8ab4135bf6b86bb00270476e6.webp'),
(3, '1NRI', 1, 'images/brands/b782e51daa66b5a08c20581e0bddf594.webp'),
(4, 'Pull & Bear', 1, 'images/brands/0cb3973cbf06a6a5480718994b02cac1.webp'),
(5, 'Samsung', 2, 'images/brands/5a2874d8fdb98f81e398ccda8c1d306d.png'),
(8, 'Adidas', 4, 'images/brands/53859e9579312952196f5a87ad46cf68.webp');

INSERT INTO `products` (`product_id`, `product_cat`, `product_brand`, `product_title`, `product_price`, `product_desc`, `product_image`, `product_keywords`) VALUES
(1, 1, 3, 'SACKLOTH TEE', 350, '', 'images/products/397e66a1855b14aa3ba16f29f730a79c.jpg', '1nri, sackloth, tee, sackloth tee, 1nri tee'),
(2, 1, 3, 'STRIPPED PANTS + BASIC TOP SET', 520, '', 'images/products/dc9ecc62f3a0349d3106bbad5393b3cd.jpg', 'Stripped pants, basic, pants, top, set'),
(3, 1, 3, 'SMS TEE', 300, '', 'images/products/481871b1a11ba48879626b616a535f36.jpg', 'SMS, scriptures, tee'),
(4, 1, 3, 'ACID WASHED SHORTS', 270, '', 'images/products/685323c768f656605e1c02bea4457acf.jpg', 'acid, washed, shorts, pants'),
(8, 4, 8, 'ADIDAS SAMBA', 1000, '', 'images/products/2aea5da036b5478349bf647a7bcc7ad5.jpg', 'adidas, samba'),
(9, 4, 8, 'NIKE SB\'S', 1000, '', 'images/products/dd2a739879d874aa9387bbb3c61c9ed6.jpg', 'nike, sb\'s, sports'),
(10, 1, 1, 'CERNUCCI BOXY TEE', 850, '', 'images/products/3244640b495ce2200184d7bb15112af1.jpg', 'cernucci,boxy,tee'),
(11, 1, 1, 'CERNUCCI ZIP UP', 1000, '', 'images/products/5530b6ea57c756738f26354480a3bfb6.jpg', 'zip up, jacket, cernucci');

COMMIT;
