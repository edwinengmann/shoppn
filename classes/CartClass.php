<?php

require_once __DIR__ . '/../core/db_class.php';

class CartClass extends Database
{
    public const CLOTHING_SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    public const FOOTWEAR_SIZES = [
        '36', '37', '38', '39', '40', '41',
        '42', '43', '44', '45', '46'
    ];
    public const MAX_QUANTITY = 99;
    private $supportsSize = false;
    private $hasCartId = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureCartSchema();
    }

    private function ensureCartSchema()
    {
        try {
            $columns = $this->getCartColumns();
            if (!isset($columns['cart_id'])) {
                try {
                    $this->conn->query(
                        'ALTER TABLE cart
                         ADD COLUMN cart_id int(11) NOT NULL AUTO_INCREMENT UNIQUE FIRST'
                    );
                } catch (mysqli_sql_exception $exception) {
                    error_log(
                        'Cart row identifier upgrade skipped: '
                            . $exception->getMessage()
                    );
                }
                $columns = $this->getCartColumns();
            }

            if (!isset($columns['size'])) {
                try {
                    $this->conn->query(
                        "ALTER TABLE cart
                         ADD COLUMN size varchar(20) NOT NULL DEFAULT '' AFTER qty"
                    );
                } catch (mysqli_sql_exception $exception) {
                    error_log(
                        'Cart size upgrade skipped: ' . $exception->getMessage()
                    );
                }
                $columns = $this->getCartColumns();
            }

            $this->hasCartId = isset($columns['cart_id']);
            $this->supportsSize = isset($columns['size']);
        } catch (mysqli_sql_exception $exception) {
            error_log('Cart schema initialization failed: ' . $exception->getMessage());
            throw $exception;
        }
    }

    private function getCartColumns()
    {
        $result = $this->conn->query('SHOW COLUMNS FROM cart');
        if (!$result) {
            throw new mysqli_sql_exception('Could not inspect the cart table.');
        }

        $columns = [];
        while ($column = $result->fetch_assoc()) {
            $columns[$column['Field']] = true;
        }
        $result->free();

        return $columns;
    }

    public static function getSizeOptions($product)
    {
        $category = strtolower((string) ($product['cat_name'] ?? ''));
        $details = strtolower(
            (string) ($product['product_title'] ?? '')
                . ' '
                . (string) ($product['product_keywords'] ?? '')
        );

        if (
            preg_match('/\b(footwear|shoes?|sneakers?|trainers?|boots?|sandals?)\b/', $category)
            || preg_match('/\b(shoes?|sneakers?|trainers?|boots?|sandals?)\b/', $details)
        ) {
            return self::FOOTWEAR_SIZES;
        }

        if (
            preg_match('/\b(clothing|clothes|apparel|fashion|garments?)\b/', $category)
            || preg_match('/\b(t-?shirts?|tees?|shirts?|hoodies|sweatshirts|jerseys|jackets|trousers|jeans|dresses|sweaters|shorts|skirts|leggings|polos)\b/', $details)
        ) {
            return self::CLOTHING_SIZES;
        }

        return [];
    }

    public function getCartItems($customerId)
    {
        $sizeField = $this->supportsSize ? 'cart.size' : "''";
        $cartIdField = $this->hasCartId ? 'cart.cart_id' : 'NULL';
        $sortField = $this->hasCartId ? 'cart.cart_id DESC' : 'cart.p_id DESC';
        $stmt = $this->conn->prepare(
            'SELECT '
                . $cartIdField
                . ' AS cart_id,
                cart.p_id AS product_id,
                '
                . $sizeField
                . ' AS size,
                cart.qty,
                products.product_title,
                products.product_price,
                products.product_image,
                products.product_keywords,
                brands.brand_name,
                categories.cat_name
             FROM cart
             INNER JOIN products
                ON cart.p_id = products.product_id
             INNER JOIN brands
                ON products.product_brand = brands.brand_id
             INNER JOIN categories
                ON products.product_cat = categories.cat_id
             WHERE cart.c_id = ?
             ORDER BY '
                . $sortField
        );
        $stmt->bind_param('i', $customerId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $items;
    }

    public function addItem($customerId, $productId, $size, $quantity, $ipAddress)
    {
        if (!$this->supportsSize) {
            if ($size !== '') {
                error_log(
                    'Cannot add sized product: live cart table has no size column.'
                );
                return false;
            }

            $stmt = $this->conn->prepare(
                'INSERT INTO cart (p_id, ip_add, c_id, qty)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'isii',
                $productId,
                $ipAddress,
                $customerId,
                $quantity
            );
            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

        if (!$this->hasCartId) {
            $stmt = $this->conn->prepare(
                'INSERT INTO cart (p_id, ip_add, c_id, qty, size)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'isiis',
                $productId,
                $ipAddress,
                $customerId,
                $quantity,
                $size
            );
            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

        try {
            $this->conn->begin_transaction();

            $find = $this->conn->prepare(
                'SELECT qty
                 FROM cart
                 WHERE c_id = ? AND p_id = ? AND size = ?
                 FOR UPDATE'
            );
            $find->bind_param('iis', $customerId, $productId, $size);
            $find->execute();
            $existing = $find->get_result()->fetch_assoc();
            $find->close();

            if ($existing) {
                $newQuantity = (int) $existing['qty'] + $quantity;
                if ($newQuantity > self::MAX_QUANTITY) {
                    $this->conn->rollback();
                    return false;
                }

                $stmt = $this->conn->prepare(
                    'UPDATE cart
                     SET qty = ?
                     WHERE c_id = ? AND p_id = ? AND size = ?'
                );
                $stmt->bind_param(
                    'iiis',
                    $newQuantity,
                    $customerId,
                    $productId,
                    $size
                );
            } else {
                $stmt = $this->conn->prepare(
                    'INSERT INTO cart (p_id, ip_add, c_id, qty, size)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->bind_param(
                    'isiis',
                    $productId,
                    $ipAddress,
                    $customerId,
                    $quantity,
                    $size
                );
            }

            $success = $stmt->execute();
            $stmt->close();
            if (!$success) {
                $this->conn->rollback();
                return false;
            }

            return $this->conn->commit();
        } catch (mysqli_sql_exception $exception) {
            $this->conn->rollback();
            error_log('Adding cart item failed: ' . $exception->getMessage());
            return false;
        }
    }

    public function updateItem(
        $customerId,
        $productId,
        $currentSize,
        $newSize,
        $quantity
    ) {
        if (!$this->supportsSize || !$this->hasCartId) {
            if ($this->supportsSize) {
                $stmt = $this->conn->prepare(
                    'UPDATE cart
                     SET size = ?, qty = ?
                     WHERE c_id = ? AND p_id = ? AND size = ?'
                );
                $stmt->bind_param(
                    'siiis',
                    $newSize,
                    $quantity,
                    $customerId,
                    $productId,
                    $currentSize
                );
            } else {
                if ($currentSize !== '' || $newSize !== '') {
                    return false;
                }

                $stmt = $this->conn->prepare(
                    'UPDATE cart
                     SET qty = ?
                     WHERE c_id = ? AND p_id = ?'
                );
                $stmt->bind_param(
                    'iii',
                    $quantity,
                    $customerId,
                    $productId
                );
            }

            $success = $stmt->execute() && $stmt->affected_rows > 0;
            $stmt->close();

            return $success;
        }

        try {
            $this->conn->begin_transaction();

            $find = $this->conn->prepare(
                'SELECT cart_id
                 FROM cart
                 WHERE c_id = ? AND p_id = ? AND size = ?
                 FOR UPDATE'
            );
            $find->bind_param('iis', $customerId, $productId, $currentSize);
            $find->execute();
            $currentItem = $find->get_result()->fetch_assoc();
            $find->close();

            if (!$currentItem) {
                $this->conn->rollback();
                return false;
            }

            $findTarget = $this->conn->prepare(
                'SELECT cart_id, qty
                 FROM cart
                 WHERE c_id = ? AND p_id = ? AND size = ?
                 FOR UPDATE'
            );
            $findTarget->bind_param('iis', $customerId, $productId, $newSize);
            $findTarget->execute();
            $targetItem = $findTarget->get_result()->fetch_assoc();
            $findTarget->close();

            if ($targetItem && $newSize !== $currentSize) {
                $combinedQuantity = (int) $targetItem['qty'] + $quantity;
                if ($combinedQuantity > self::MAX_QUANTITY) {
                    $this->conn->rollback();
                    return false;
                }

                $updateTarget = $this->conn->prepare(
                    'UPDATE cart SET qty = ? WHERE cart_id = ? AND c_id = ?'
                );
                $targetId = (int) $targetItem['cart_id'];
                $updateTarget->bind_param(
                    'iii',
                    $combinedQuantity,
                    $targetId,
                    $customerId
                );
                $success = $updateTarget->execute();
                $updateTarget->close();

                if ($success) {
                    $deleteCurrent = $this->conn->prepare(
                        'DELETE FROM cart WHERE cart_id = ? AND c_id = ?'
                    );
                    $currentId = (int) $currentItem['cart_id'];
                    $deleteCurrent->bind_param('ii', $currentId, $customerId);
                    $success = $deleteCurrent->execute();
                    $deleteCurrent->close();
                }
            } else {
                $update = $this->conn->prepare(
                    'UPDATE cart
                     SET size = ?, qty = ?
                     WHERE cart_id = ? AND c_id = ?'
                );
                $currentId = (int) $currentItem['cart_id'];
                $update->bind_param(
                    'siii',
                    $newSize,
                    $quantity,
                    $currentId,
                    $customerId
                );
                $success = $update->execute();
                $update->close();
            }

            if (!$success) {
                $this->conn->rollback();
                return false;
            }

            return $this->conn->commit();
        } catch (mysqli_sql_exception $exception) {
            $this->conn->rollback();
            error_log('Updating cart item failed: ' . $exception->getMessage());
            return false;
        }
    }

    public function removeItem($customerId, $productId, $size)
    {
        try {
            if ($this->supportsSize) {
                $stmt = $this->conn->prepare(
                    'DELETE FROM cart
                     WHERE c_id = ? AND p_id = ? AND size = ?'
                );
                $stmt->bind_param('iis', $customerId, $productId, $size);
            } else {
                if ($size !== '') {
                    return false;
                }

                $stmt = $this->conn->prepare(
                    'DELETE FROM cart WHERE c_id = ? AND p_id = ?'
                );
                $stmt->bind_param('ii', $customerId, $productId);
            }
            $success = $stmt->execute() && $stmt->affected_rows > 0;
            $stmt->close();

            return $success;
        } catch (mysqli_sql_exception $exception) {
            error_log('Removing cart item failed: ' . $exception->getMessage());
            return false;
        }
    }
}