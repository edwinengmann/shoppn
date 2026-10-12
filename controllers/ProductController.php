<?php

require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new ProductClass();
    }

    public function addBrand($name, $categoryId, $imagePath = null)
    {
        return $this->productModel->addBrand($name, $categoryId, $imagePath);
    }

    public function getAllBrands()
    {
        return $this->productModel->getAllBrands();
    }

    public function getBrandsByCategory($categoryId = null)
    {
        return $this->productModel->getBrandsByCategory($categoryId);
    }

    public function getBrandById($id)
    {
        return $this->productModel->getBrandById($id);
    }

    public function updateBrand($id, $name, $categoryId, $imagePath = null)
    {
        return $this->productModel->updateBrand(
            $id,
            $name,
            $categoryId,
            $imagePath
        );
    }

    public function deleteBrand($id)
    {
        return $this->productModel->deleteBrand($id);
    }

    public function addCategory($name)
    {
        return $this->productModel->addCategory($name);
    }

    public function getAllCategories()
    {
        return $this->productModel->getAllCategories();
    }

    public function getAllProducts()
    {
        return $this->productModel->getAllProducts();
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
        return $this->productModel->addProduct(
            $categoryId,
            $brandId,
            $title,
            $price,
            $description,
            $imagePath,
            $keywords
        );
    }

    public function getCatalogProducts(
        $categoryId = null,
        $brandId = null,
        $search = null
    )
    {
        return $this->productModel->getCatalogProducts(
            $categoryId,
            $brandId,
            $search
        );
    }

    public function getProductById($id)
    {
        return $this->productModel->getProductById($id);
    }

    public function getCategoryById($id)
    {
        return $this->productModel->getCategoryById($id);
    }

    public function updateCategory($id, $name)
    {
        return $this->productModel->updateCategory($id, $name);
    }

    public function deleteCategory($id)
    {
        return $this->productModel->deleteCategory($id);
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
        return $this->productModel->updateProduct(
            $id,
            $categoryId,
            $brandId,
            $title,
            $price,
            $description,
            $imagePath,
            $keywords
        );
    }

    public function deleteProduct($id)
    {
        return $this->productModel->deleteProduct($id);
    }
}

?>
