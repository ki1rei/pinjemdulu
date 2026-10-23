<?php
function json_out($data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Baca body JSON; kalau bukan JSON, pakai form biasa ($_POST)
function input(): array {
    $json = json_decode(file_get_contents('php://input'), true);
    return is_array($json) ? $json : $_POST;
}