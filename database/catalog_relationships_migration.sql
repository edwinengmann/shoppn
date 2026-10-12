ALTER TABLE `brands`
  ADD COLUMN `brand_cat` int(11) DEFAULT NULL AFTER `brand_name`,
  ADD KEY `brand_cat` (`brand_cat`),
  ADD CONSTRAINT `brands_ibfk_1`
    FOREIGN KEY (`brand_cat`) REFERENCES `categories` (`cat_id`);

UPDATE `brands` AS b
INNER JOIN (
  SELECT `product_brand`, MIN(`product_cat`) AS `category_id`
  FROM `products`
  GROUP BY `product_brand`
  HAVING COUNT(DISTINCT `product_cat`) = 1
) AS existing_brand_categories
  ON existing_brand_categories.`product_brand` = b.`brand_id`
SET b.`brand_cat` = existing_brand_categories.`category_id`;
