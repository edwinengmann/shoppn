ALTER TABLE `customer`
  ADD COLUMN `customer_address` varchar(255) NOT NULL DEFAULT ''
  AFTER `customer_contact`;
