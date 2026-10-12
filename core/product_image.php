<?php

function save_product_image_upload($file)
{
    if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
        return [
            'path' => null,
            'error' => 'The selected product image could not be uploaded.'
        ];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'path' => null,
            'error' => 'The product image upload failed. Please choose it again.'
        ];
    }

    if (
        !isset($file['tmp_name'], $file['size'])
        || !is_uploaded_file($file['tmp_name'])
        || $file['size'] < 1
        || $file['size'] > 5 * 1024 * 1024
    ) {
        return [
            'path' => null,
            'error' => 'Choose an image no larger than 5 MB.'
        ];
    }

    $imageInfo = getimagesize($file['tmp_name']);
    $extensions = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_WEBP => 'webp'
    ];

    if ($imageInfo === false || !isset($extensions[$imageInfo[2]])) {
        return [
            'path' => null,
            'error' => 'Choose a valid JPG, PNG, GIF, or WebP image.'
        ];
    }

    $directory = dirname(__DIR__) . '/images/products';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        return [
            'path' => null,
            'error' => 'The product image directory could not be created.'
        ];
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$imageInfo[2]];
    $destination = $directory . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'path' => null,
            'error' => 'The product image could not be saved. Please try again.'
        ];
    }

    return [
        'path' => 'images/products/' . $filename,
        'error' => null
    ];
}

function delete_product_image($path)
{
    if (
        !is_string($path)
        || strpos($path, 'images/products/') !== 0
        || basename($path) !== substr($path, strlen('images/products/'))
    ) {
        return;
    }

    $file = dirname(__DIR__) . '/' . $path;
    if (is_file($file)) {
        unlink($file);
    }
}
