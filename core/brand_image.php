<?php

function save_brand_image_upload($file)
{
    if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
        return [
            'path' => null,
            'error' => 'The selected logo could not be uploaded.'
        ];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'path' => null,
            'error' => 'The logo upload failed. Please choose the image again.'
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

    $directory = dirname(__DIR__) . '/images/brands';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        return [
            'path' => null,
            'error' => 'The logo upload directory could not be created.'
        ];
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$imageInfo[2]];
    $destination = $directory . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'path' => null,
            'error' => 'The logo could not be saved. Please try again.'
        ];
    }

    return [
        'path' => 'images/brands/' . $filename,
        'error' => null
    ];
}

function delete_brand_image($path)
{
    if (
        !is_string($path)
        || strpos($path, 'images/brands/') !== 0
        || basename($path) !== substr($path, strlen('images/brands/'))
    ) {
        return;
    }

    $file = dirname(__DIR__) . '/' . $path;
    if (is_file($file)) {
        unlink($file);
    }
}
