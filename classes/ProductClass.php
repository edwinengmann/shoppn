<?php

require_once __DIR__ . '/../core/db_class.php';

class ProductClass extends Database
{
    public function addBrand($name, $categoryId, $imagePath = null)
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO brands (brand_name, brand_cat, brand_image) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('sis', $name, $categoryId, $imagePath);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function getAllBrands()
    {
        $result = $this->conn->query(
            'SELECT brands.*, categories.cat_name
             FROM brands
             LEFT JOIN categories ON brands.brand_cat = categories.cat_id
             ORDER BY brands.brand_name ASC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getBrandsByCategory($categoryId = null)
    {
        if ($categoryId === null) {
            return $this->getAllBrands();
        }

        $stmt = $this->conn->prepare(
            'SELECT brands.*, categories.cat_name
             FROM brands
             LEFT JOIN categories ON brands.brand_cat = categories.cat_id
             WHERE brands.brand_cat = ?
             ORDER BY brands.brand_name ASC'
        );
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $brands = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $brands;
    }

    public function getBrandById($id)
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM brands WHERE brand_id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $brand = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $brand ?: false;
    }

    public function updateBrand($id, $name, $categoryId, $imagePath = null)
    {
        $this->conn->begin_transaction();

        $stmt = $this->conn->prepare(
            'UPDATE brands
             SET brand_name = ?, brand_cat = ?, brand_image = ?
             WHERE brand_id = ?'
        );
        $stmt->bind_param('sisi', $name, $categoryId, $imagePath, $id);
        $success = $stmt->execute();
        $stmt->close();

        if (!$success) {
            $this->conn->rollback();
            return false;
        }

        $productStmt = $this->conn->prepare(
            'UPDATE products SET product_cat = ? WHERE product_brand = ?'
        );
        $productStmt->bind_param('ii', $categoryId, $id);
        $success = $productStmt->execute();
        $productStmt->close();

        if (!$success) {
            $this->conn->rollback();
            return false;
        }

        return $this->conn->commit();
    }

    public function deleteBrand($id)
    {
        $this->conn->begin_transaction();

        try {
            $brandStmt = $this->conn->prepare(
                'SELECT brand_image FROM brands WHERE brand_id = ?'
            );
            $brandStmt->bind_param('i', $id);
            $brandStmt->execute();
            $brand = $brandStmt->get_result()->fetch_assoc();
            $brandStmt->close();

            if (!$brand) {
                $this->conn->rollback();
                return ['success' => false, 'error' => 'The selected brand no longer exists.'];
            }

            $imageStmt = $this->conn->prepare(
                'SELECT product_image FROM products WHERE product_brand = ?'
            );
            $imageStmt->bind_param('i', $id);
            $imageStmt->execute();
            $productImages = $imageStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $imageStmt->close();

            $historyStmt = $this->conn->prepare(
                'SELECT COUNT(*) AS order_count
                 FROM orderdetails
                 INNER JOIN products
                    ON orderdetails.product_id = products.product_id
                 WHERE products.product_brand = ?'
            );
            $historyStmt->bind_param('i', $id);
            $historyStmt->execute();
            $hasOrderHistory = (int) $historyStmt->get_result()
                ->fetch_assoc()['order_count'] > 0;
            $historyStmt->close();

            if ($hasOrderHistory) {
                $this->conn->rollback();
                return [
                    'success' => false,
                    'error' => 'This brand has products in order history. Those products cannot be deleted.'
                ];
            }

            $deleteProducts = $this->conn->prepare(
                'DELETE FROM products WHERE product_brand = ?'
            );
            $deleteProducts->bind_param('i', $id);
            $deleteProducts->execute();
            $deleteProducts->close();

            $deleteBrand = $this->conn->prepare(
                'DELETE FROM brands WHERE brand_id = ?'
            );
            $deleteBrand->bind_param('i', $id);
            $deleteBrand->execute();
            $deleteBrand->close();

            if (!$this->conn->commit()) {
                throw new mysqli_sql_exception('Could not commit brand deletion.');
            }

            return [
                'success' => true,
                'images' => array_merge(
                    [$brand['brand_image']],
                    array_column($productImages, 'product_image')
                )
            ];
        } catch (mysqli_sql_exception $exception) {
            $this->conn->rollback();
            error_log('Brand deletion failed: ' . $exception->getMessage());
            return [
                'success' => false,
                'error' => 'The brand and its products could not be deleted.'
            ];
        }
    }

    public function addCategory($name)
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO categories (cat_name) VALUES (?)'
        );
        $stmt->bind_param('s', $name);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function getAllCategories()
    {
        $result = $this->conn->query(
            'SELECT * FROM categories ORDER BY cat_name ASC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllProducts()
    {
        $result = $this->conn->query(
            'SELECT
                products.product_id,
                products.product_cat,
                products.product_brand,
                products.product_title,
                products.product_price,
                products.product_desc,
                products.product_image,
                products.product_keywords,
                brands.brand_name,
                brands.brand_image,
                categories.cat_name
             FROM products
             INNER JOIN brands
                ON products.product_brand = brands.brand_id
             INNER JOIN categories
                ON products.product_cat = categories.cat_id
             ORDER BY products.product_title ASC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addProduct(
        $categoryId,
        $brandId,
        $title,
        $price,
        $description,
        $imagePath,
        $keywords
    ) {
        $stmt = $this->conn->prepare(
            'INSERT INTO products (
                product_cat,
                product_brand,
                product_title,
                product_price,
                product_desc,
                product_image,
                product_keywords
             ) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'iisdsss',
            $categoryId,
            $brandId,
            $title,
            $price,
            $description,
            $imagePath,
            $keywords
        );
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateProduct(
        $id,
        $categoryId,
        $brandId,
        $title,
        $price,
        $description,
        $imagePath,
        $keywords
    ) {
        $stmt = $this->conn->prepare(
            'UPDATE products
             SET product_cat = ?,
                 product_brand = ?,
                 product_title = ?,
                 product_price = ?,
                 product_desc = ?,
                 product_image = ?,
                 product_keywords = ?
             WHERE product_id = ?'
        );
        $stmt->bind_param(
            'iisdsssi',
            $categoryId,
            $brandId,
            $title,
            $price,
            $description,
            $imagePath,
            $keywords,
            $id
        );
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function deleteProduct($id)
    {
        $imageStmt = $this->conn->prepare(
            'SELECT product_image FROM products WHERE product_id = ?'
        );
        $imageStmt->bind_param('i', $id);
        $imageStmt->execute();
        $product = $imageStmt->get_result()->fetch_assoc();
        $imageStmt->close();

        if (!$product) {
            return ['success' => false, 'error' => 'The selected product no longer exists.'];
        }

        $historyStmt = $this->conn->prepare(
            'SELECT COUNT(*) AS order_count
             FROM orderdetails
             WHERE product_id = ?'
        );
        $historyStmt->bind_param('i', $id);
        $historyStmt->execute();
        $hasOrderHistory = (int) $historyStmt->get_result()
            ->fetch_assoc()['order_count'] > 0;
        $historyStmt->close();

        if ($hasOrderHistory) {
            return [
                'success' => false,
                'error' => 'This product is in order history and cannot be deleted.'
            ];
        }

        try {
            $deleteStmt = $this->conn->prepare(
                'DELETE FROM products WHERE product_id = ?'
            );
            $deleteStmt->bind_param('i', $id);
            $deleteStmt->execute();
            $deleteStmt->close();

            return ['success' => true, 'images' => [$product['product_image']]];
        } catch (mysqli_sql_exception $exception) {
            error_log('Product deletion failed: ' . $exception->getMessage());
            return [
                'success' => false,
                'error' => 'The product could not be deleted.'
            ];
        }
    }

    public function deleteCategory($id)
    {
        $this->conn->begin_transaction();

        try {
            $categoryStmt = $this->conn->prepare(
                'SELECT cat_id FROM categories WHERE cat_id = ?'
            );
            $categoryStmt->bind_param('i', $id);
            $categoryStmt->execute();
            $category = $categoryStmt->get_result()->fetch_assoc();
            $categoryStmt->close();

            if (!$category) {
                $this->conn->rollback();
                return [
                    'success' => false,
                    'error' => 'The selected category no longer exists.'
                ];
            }

            $brandStmt = $this->conn->prepare(
                'SELECT brand_id, brand_image FROM brands WHERE brand_cat = ?'
            );
            $brandStmt->bind_param('i', $id);
            $brandStmt->execute();
            $brands = $brandStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $brandStmt->close();
            $imageStmt = $this->conn->prepare(
                'SELECT product_image
                 FROM products
                 WHERE product_cat = ?
                    OR product_brand IN (
                        SELECT brand_id FROM brands WHERE brand_cat = ?
                    )'
            );
            $imageStmt->bind_param('ii', $id, $id);
            $imageStmt->execute();
            $productImages = $imageStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $imageStmt->close();

            $historyStmt = $this->conn->prepare(
                'SELECT COUNT(*) AS order_count
                 FROM orderdetails
                 INNER JOIN products
                    ON orderdetails.product_id = products.product_id
                 WHERE products.product_cat = ?
                    OR products.product_brand IN (
                        SELECT brand_id FROM brands WHERE brand_cat = ?
                    )'
            );
            $historyStmt->bind_param('ii', $id, $id);
            $historyStmt->execute();
            $hasOrderHistory = (int) $historyStmt->get_result()
                ->fetch_assoc()['order_count'] > 0;
            $historyStmt->close();

            if ($hasOrderHistory) {
                $this->conn->rollback();
                return [
                    'success' => false,
                    'error' => 'This category has products in order history. Those products cannot be deleted.'
                ];
            }

            $deleteProducts = $this->conn->prepare(
                'DELETE FROM products
                 WHERE product_cat = ?
                    OR product_brand IN (
                        SELECT brand_id FROM brands WHERE brand_cat = ?
                    )'
            );
            $deleteProducts->bind_param('ii', $id, $id);
            $deleteProducts->execute();
            $deleteProducts->close();

            $deleteBrands = $this->conn->prepare(
                'DELETE FROM brands WHERE brand_cat = ?'
            );
            $deleteBrands->bind_param('i', $id);
            $deleteBrands->execute();
            $deleteBrands->close();

            $deleteCategory = $this->conn->prepare(
                'DELETE FROM categories WHERE cat_id = ?'
            );
            $deleteCategory->bind_param('i', $id);
            $deleteCategory->execute();
            $deleteCategory->close();

            if (!$this->conn->commit()) {
                throw new mysqli_sql_exception('Could not commit category deletion.');
            }

            return [
                'success' => true,
                'images' => array_merge(
                    array_column($brands, 'brand_image'),
                    array_column($productImages, 'product_image')
                )
            ];
        } catch (mysqli_sql_exception $exception) {
            $this->conn->rollback();
            error_log('Category deletion failed: ' . $exception->getMessage());
            return [
                'success' => false,
                'error' => 'The category, its brands, and their products could not be deleted.'
            ];
        }
    }

    public function getProductById($id)
    {
        $stmt = $this->conn->prepare(
            'SELECT
                products.product_id,
                products.product_cat,
                products.product_brand,
                products.product_title,
                products.product_price,
                products.product_desc,
                products.product_image,
                products.product_keywords,
                brands.brand_name,
                brands.brand_image,
                categories.cat_name
             FROM products
             INNER JOIN brands
                ON products.product_brand = brands.brand_id
             INNER JOIN categories
                ON products.product_cat = categories.cat_id
             WHERE products.product_id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $product ?: false;
    }

    public function getCatalogProducts(
        $categoryId = null,
        $brandId = null,
        $search = null
    )
    {
        $stmt = $this->conn->prepare(
            'SELECT
                products.product_id,
                products.product_cat,
                products.product_brand,
                products.product_title,
                products.product_price,
                products.product_desc,
                products.product_image,
                products.product_keywords,
                brands.brand_name,
                brands.brand_image,
                categories.cat_name
             FROM products
             INNER JOIN brands
                ON products.product_brand = brands.brand_id
             INNER JOIN categories
                ON products.product_cat = categories.cat_id
             WHERE (? IS NULL OR products.product_cat = ?)
               AND (? IS NULL OR products.product_brand = ?)
               AND (
                    ? IS NULL
                    OR products.product_title LIKE ?
                    OR products.product_desc LIKE ?
                    OR products.product_keywords LIKE ?
                    OR brands.brand_name LIKE ?
                    OR categories.cat_name LIKE ?
               )
             ORDER BY RAND()'
        );
        $searchPattern = $search === null ? null : '%' . $search . '%';
        $stmt->bind_param(
            'iiiissssss',
            $categoryId,
            $categoryId,
            $brandId,
            $brandId,
            $search,
            $searchPattern,
            $searchPattern,
            $searchPattern,
            $searchPattern,
            $searchPattern
        );
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $products;
    }

    public function getCategoryById($id)
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM categories WHERE cat_id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $category = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $category ?: false;
    }

    public function updateCategory($id, $name)
    {
        $stmt = $this->conn->prepare(
            'UPDATE categories SET cat_name = ? WHERE cat_id = ?'
        );
        $stmt->bind_param('si', $name, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}

?>
