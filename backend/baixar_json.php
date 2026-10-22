<?php
require_once __DIR__ . '/config.php';

if (!isset($_GET['id'])) {
    die('ID não informado.');
}

$id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id']);
$arquivo = TMP_DIR . '/' . $id . '.json';

if (!file_exists($arquivo)) {
    die('Arquivo não encontrado.');
}

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="resultado_' . $id . '.json"');
header('Content-Length: ' . filesize($arquivo));
readfile($arquivo);
exit;