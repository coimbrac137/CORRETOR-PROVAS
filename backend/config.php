<?php
define('TMP_DIR', __DIR__ . '/../tmp');

if (!is_dir(TMP_DIR)) {
    mkdir(TMP_DIR, 0777, true);
}

function gerar_id() {
    return date('Ymd_His') . '_' . bin2hex(random_bytes(4));
}

function resposta_json($dados) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}