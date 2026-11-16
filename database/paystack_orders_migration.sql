 webhook URL.
CREATE TABLE IF NOT EXISTS `shop_orders` (
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

CREATE TABLE IF NOT EXISTS `shop_order_items` (
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

CREATE TABLE IF NOT EXISTS `shop_payments` (
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
