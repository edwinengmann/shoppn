<?php

require_once __DIR__ . '/../core/db_class.php';

class OrderPaymentClass extends Database
{
    public function createPendingOrder($customerId, $customer, $items, $reference, $channels)
    {
        $this->conn->begin_transaction();

        try {
            $amountMinor = 0;
            foreach ($items as $item) {
                $amountMinor += (int) round(
                    (float) $item['product_price'] * 100
                ) * (int) $item['qty'];
            }

            if ($amountMinor < 1) {
                throw new RuntimeException('The order total must be greater than zero.');
            }

            $amount = number_format($amountMinor / 100, 2, '.', '');
            $name = (string) $customer['customer_name'];
            $email = (string) $customer['customer_email'];
            $contact = (string) $customer['customer_contact'];
            $address = (string) $customer['customer_address'];
            $city = (string) $customer['customer_city'];
            $country = (string) $customer['customer_country'];
            $status = 'pending';
            $currency = 'GHS';

            $order = $this->conn->prepare(
                'INSERT INTO shop_orders
                    (customer_id, customer_name, customer_email, customer_contact,
                     delivery_address, delivery_city, delivery_country, amount,
                     currency, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $order->bind_param(
                'isssssssss',
                $customerId,
                $name,
                $email,
                $contact,
                $address,
                $city,
                $country,
                $amount,
                $currency,
                $status
            );
            $order->execute();
            $orderId = (int) $this->conn->insert_id;
            $order->close();

            $itemInsert = $this->conn->prepare(
                'INSERT INTO shop_order_items
                    (order_id, product_id, product_title, unit_price, quantity, size)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $title = (string) $item['product_title'];
                $unitPrice = number_format((float) $item['product_price'], 2, '.', '');
                $quantity = (int) $item['qty'];
                $size = (string) $item['size'];
                $itemInsert->bind_param(
                    'iissis',
                    $orderId,
                    $productId,
                    $title,
                    $unitPrice,
                    $quantity,
                    $size
                );
                $itemInsert->execute();
            }
            $itemInsert->close();

            $channelList = implode(',', $channels);
            $payment = $this->conn->prepare(
                'INSERT INTO shop_payments
                    (order_id, reference, amount, currency, offered_channels, status)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $payment->bind_param(
                'isssss',
                $orderId,
                $reference,
                $amount,
                $currency,
                $channelList,
                $status
            );
            $payment->execute();
            $payment->close();

            $this->conn->commit();

            return ['order_id' => $orderId, 'amount_minor' => $amountMinor];
        } catch (Throwable $exception) {
            $this->conn->rollback();
            throw $exception;
        }
    }

    public function getPaymentByReference($reference)
    {
        $stmt = $this->conn->prepare(
            'SELECT shop_payments.*, shop_orders.customer_id
             FROM shop_payments
             INNER JOIN shop_orders
                ON shop_orders.order_id = shop_payments.order_id
             WHERE shop_payments.reference = ?
             LIMIT 1'
        );
        $stmt->bind_param('s', $reference);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $payment ?: false;
    }

    public function confirmPayment($reference, $amountMinor, $currency, $transactionId, $method)
    {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare(
                'SELECT shop_payments.order_id, shop_payments.amount,
                        shop_payments.currency, shop_payments.status,
                        shop_orders.customer_id
                 FROM shop_payments
                 INNER JOIN shop_orders
                    ON shop_orders.order_id = shop_payments.order_id
                 WHERE shop_payments.reference = ?
                 FOR UPDATE'
            );
            $stmt->bind_param('s', $reference);
            $stmt->execute();
            $payment = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (
                !$payment
                || strtoupper((string) $payment['currency']) !== strtoupper($currency)
                || (int) round((float) $payment['amount'] * 100) !== (int) $amountMinor
            ) {
                $this->conn->rollback();
                return false;
            }

            if ($payment['status'] !== 'paid') {
                $status = 'paid';
                $updatePayment = $this->conn->prepare(
                    'UPDATE shop_payments
                     SET status = ?, transaction_id = ?, payment_method = ?, paid_at = NOW()
                     WHERE reference = ?'
                );
                $updatePayment->bind_param(
                    'ssss',
                    $status,
                    $transactionId,
                    $method,
                    $reference
                );
                $updatePayment->execute();
                $updatePayment->close();

                $updateOrder = $this->conn->prepare(
                    'UPDATE shop_orders SET status = ? WHERE order_id = ?'
                );
                $updateOrder->bind_param('si', $status, $payment['order_id']);
                $updateOrder->execute();
                $updateOrder->close();

                $orderItems = $this->conn->prepare(
                    'SELECT product_id, size, quantity
                     FROM shop_order_items
                     WHERE order_id = ?'
                );
                $orderItems->bind_param('i', $payment['order_id']);
                $orderItems->execute();
                $purchasedItems = $orderItems->get_result()->fetch_all(MYSQLI_ASSOC);
                $orderItems->close();

                $removePurchasedLine = $this->conn->prepare(
                    'DELETE FROM cart
                     WHERE c_id = ? AND p_id = ? AND size = ? AND qty <= ?'
                );
                $reduceCartQuantity = $this->conn->prepare(
                    'UPDATE cart
                     SET qty = qty - ?
                     WHERE c_id = ? AND p_id = ? AND size = ? AND qty > ?'
                );
                foreach ($purchasedItems as $item) {
                    $productId = (int) $item['product_id'];
                    $size = (string) $item['size'];
                    $quantity = (int) $item['quantity'];
                    $customerId = (int) $payment['customer_id'];
                    $removePurchasedLine->bind_param(
                        'iisi',
                        $customerId,
                        $productId,
                        $size,
                        $quantity
                    );
                    $removePurchasedLine->execute();
                    $reduceCartQuantity->bind_param(
                        'iiisi',
                        $quantity,
                        $customerId,
                        $productId,
                        $size,
                        $quantity
                    );
                    $reduceCartQuantity->execute();
                }
                $removePurchasedLine->close();
                $reduceCartQuantity->close();
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $exception) {
            $this->conn->rollback();
            throw $exception;
        }
    }

    public function markPaymentFailed($reference, $transactionId = '')
    {
        $status = 'failed';
        $payment = $this->conn->prepare(
            'UPDATE shop_payments
             SET status = ?, transaction_id = ?
             WHERE reference = ? AND status <> "paid"'
        );
        $payment->bind_param('sss', $status, $transactionId, $reference);
        $payment->execute();
        $payment->close();

        $order = $this->conn->prepare(
            'UPDATE shop_orders
             INNER JOIN shop_payments
                ON shop_payments.order_id = shop_orders.order_id
             SET shop_orders.status = ?
             WHERE shop_payments.reference = ?
                AND shop_payments.status = "failed"
                AND shop_orders.status <> "paid"'
        );
        $order->bind_param('ss', $status, $reference);
        $order->execute();
        $order->close();
    }

    public function getAllOrders()
    {
        $result = $this->conn->query(
            'SELECT shop_orders.order_id, shop_orders.customer_name,
                    shop_orders.customer_email, shop_orders.customer_contact,
                    shop_orders.delivery_address, shop_orders.delivery_city,
                    shop_orders.delivery_country, shop_orders.amount,
                    shop_orders.currency, shop_orders.status AS order_status,
                    shop_orders.created_at, shop_payments.reference,
                    shop_payments.status AS payment_status,
                    shop_payments.offered_channels,
                    shop_payments.payment_method,
                    shop_payments.transaction_id, shop_payments.paid_at,
                    shop_order_items.product_title, shop_order_items.unit_price,
                    shop_order_items.quantity, shop_order_items.size
             FROM shop_orders
             INNER JOIN shop_payments
                ON shop_payments.order_id = shop_orders.order_id
             LEFT JOIN shop_order_items
                ON shop_order_items.order_id = shop_orders.order_id
             ORDER BY shop_orders.created_at DESC, shop_orders.order_id DESC'
        );

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orderId = (int) $row['order_id'];
            if (!isset($orders[$orderId])) {
                $orders[$orderId] = [
                    'order_id' => $orderId,
                    'customer_name' => $row['customer_name'],
                    'customer_email' => $row['customer_email'],
                    'customer_contact' => $row['customer_contact'],
                    'delivery_address' => $row['delivery_address'],
                    'delivery_city' => $row['delivery_city'],
                    'delivery_country' => $row['delivery_country'],
                    'amount' => $row['amount'],
                    'currency' => $row['currency'],
                    'order_status' => $row['order_status'],
                    'created_at' => $row['created_at'],
                    'reference' => $row['reference'],
                    'payment_status' => $row['payment_status'],
                    'offered_channels' => $row['offered_channels'],
                    'payment_method' => $row['payment_method'],
                    'transaction_id' => $row['transaction_id'],
                    'paid_at' => $row['paid_at'],
                    'items' => []
                ];
            }
            if ($row['product_title'] !== null) {
                $orders[$orderId]['items'][] = [
                    'title' => $row['product_title'],
                    'unit_price' => $row['unit_price'],
                    'quantity' => $row['quantity'],
                    'size' => $row['size']
                ];
            }
        }
        $result->free();

        return array_values($orders);
    }
}
