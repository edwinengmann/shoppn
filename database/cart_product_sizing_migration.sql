ALTER TABLE `cart`
  MODIFY COLUMN `size` varchar(20) NOT NULL DEFAULT '';

CREATE TEMPORARY TABLE `cart_normalized_sizes`
SELECT
  `c_id`,
  `p_id`,
  IF(`size` = 'One size', '', `size`) AS `new_size`,
  MIN(`cart_id`) AS `survivor_id`,
  SUM(`qty`) AS `total_qty`
FROM `cart`
WHERE `c_id` IS NOT NULL
GROUP BY `c_id`, `p_id`, IF(`size` = 'One size', '', `size`);

DELETE duplicate
FROM `cart` AS duplicate
INNER JOIN `cart_normalized_sizes` AS merged
  ON duplicate.`c_id` = merged.`c_id`
  AND duplicate.`p_id` = merged.`p_id`
  AND IF(duplicate.`size` = 'One size', '', duplicate.`size`) = merged.`new_size`
  AND duplicate.`cart_id` <> merged.`survivor_id`;

UPDATE `cart` AS survivor
INNER JOIN `cart_normalized_sizes` AS merged
  ON survivor.`cart_id` = merged.`survivor_id`
SET survivor.`size` = merged.`new_size`,
    survivor.`qty` = merged.`total_qty`;

DROP TEMPORARY TABLE `cart_normalized_sizes`;

UPDATE `cart`
SET `size` = ''
WHERE `size` = 'One size';
