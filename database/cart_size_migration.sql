ALTER TABLE `cart`
  ADD COLUMN `cart_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
  ADD COLUMN `size` varchar(20) NOT NULL DEFAULT '' AFTER `qty`;

CREATE TEMPORARY TABLE `cart_merge`
SELECT
  `c_id`,
  `p_id`,
  `size`,
  MIN(`cart_id`) AS `survivor_id`,
  SUM(`qty`) AS `total_qty`
FROM `cart`
WHERE `c_id` IS NOT NULL
GROUP BY `c_id`, `p_id`, `size`;

UPDATE `cart` AS survivor
INNER JOIN `cart_merge` AS grouped_cart
  ON grouped_cart.`survivor_id` = survivor.`cart_id`
SET survivor.`qty` = grouped_cart.`total_qty`;

DELETE duplicate
FROM `cart` AS duplicate
INNER JOIN `cart` AS survivor
  ON duplicate.`c_id` = survivor.`c_id`
  AND duplicate.`p_id` = survivor.`p_id`
  AND duplicate.`size` = survivor.`size`
  AND duplicate.`cart_id` > survivor.`cart_id`
WHERE duplicate.`c_id` IS NOT NULL;

DROP TEMPORARY TABLE `cart_merge`;

ALTER TABLE `cart`
  ADD UNIQUE KEY `cart_customer_product_size` (`c_id`, `p_id`, `size`);
