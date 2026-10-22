<?php
define('BASE_DIR', dirname(__DIR__));
define('TMP_DIR', BASE_DIR . '/tmp');
define('PYTHON_DIR', BASE_DIR . '/visao_computacional');
define('PYTHON_BIN', 'C:\\Users\\estag\\AppData\\Local\\Programs\\Python\\Python315\\python.exe');

if (!is_dir(TMP_DIR)) {
    mkdir(TMP_DIR, 0777, true);
}

function resposta_json($dados) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function gerar_id() {
    return date('Ymd_His') . '_' . bin2hex(random_bytes(4));
}

function limpar_arquivo($caminho) {
    if (file_exists($caminho)) @unlink($caminho);
}