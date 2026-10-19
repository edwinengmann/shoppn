<?php

require_once __DIR__ . '/../classes/CartClass.php';

class CartController
{
    private $cartModel;

    public function __construct()
    {
        $this->cartModel = new CartClass();
    }

    public function getCartItems($customerId)
    {
        return $this->cartModel->getCartItems($customerId);
    }

    public function addItem($customerId, $productId, $size, $quantity, $ipAddress)
    {
        return $this->cartModel->addItem(
            $customerId,
            $productId,
            $size,
            $quantity,
            $ipAddress
        );
    }

    public function updateItem(
        $customerId,
        $productId,
        $currentSize,
        $newSize,
        $quantity
    ) {
        return $this->cartModel->updateItem(
            $customerId,
            $productId,
            $currentSize,
            $newSize,
            $quantity
        );
    }

    public function removeItem($customerId, $productId, $size)
    {
        return $this->cartModel->removeItem($customerId, $productId, $size);
    }
}