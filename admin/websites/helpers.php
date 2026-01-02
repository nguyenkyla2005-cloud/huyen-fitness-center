<?php
/**
 * Helpers for Websites module.
 */

function hf_base_url(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';

    // Project is usually deployed under /BTL
    $pos = strpos($script, '/BTL/');
    if ($pos !== false) {
        return substr($script, 0, $pos + 4); // "/BTL"
    }

    // If project is deployed at web root
    return '';
}

function hf_asset_url(string $path): string {
    $path = trim($path);
    if ($path === '') {
        return '';
    }

    // absolute or external
    if (preg_match('#^(https?:)?//#i', $path)) {
        return $path;
    }
    if (substr($path, 0, 1) === '/') {
        return $path;
    }

    // Uploaded files are stored as "uploads/..." (relative to project root)
    if (strpos($path, 'uploads/') === 0) {
        $base = rtrim(hf_base_url(), '/');
        return ($base !== '' ? $base : '') . '/' . $path;
    }

    // keep legacy relative paths (e.g. images/z1.jpg)
    return $path;
}

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function hf_slugify(string $text): string {
    $text = trim($text);
    if ($text === '') return 'item';

    // lowercase
    if (function_exists('mb_strtolower')) {
        $text = mb_strtolower($text, 'UTF-8');
    } else {
        $text = strtolower($text);
    }

    // remove diacritics
    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($converted !== false) {
        $text = $converted;
    }

    // replace non-alnum by dash
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');

    return $text !== '' ? $text : 'item';
}

function hf_save_upload(array $file, string $subdir): array {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Vui lòng chọn file ảnh hợp lệ.'];
    }

    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed, true)) {
        return ['ok' => false, 'error' => 'Chỉ cho phép ảnh: JPG, PNG, WEBP, GIF.'];
    }

    $root = realpath(__DIR__ . '/../../'); // /BTL
    if ($root === false) {
        return ['ok' => false, 'error' => 'Không xác định được thư mục gốc dự án.'];
    }

    $dir = $root . '/uploads/' . trim($subdir, '/');
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
            return ['ok' => false, 'error' => 'Không thể tạo thư mục uploads.'];
        }
    }

    try {
        $rand = bin2hex(random_bytes(4));
    } catch (Exception $e) {
        $rand = (string)mt_rand(1000, 9999);
    }

    $filename = time() . '_' . $rand . '.' . $ext;
    $dest = $dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['ok' => false, 'error' => 'Upload thất bại.'];
    }

    return ['ok' => true, 'path' => 'uploads/' . trim($subdir, '/') . '/' . $filename];
}
