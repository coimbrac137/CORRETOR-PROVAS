<?php
require_once __DIR__ . '/config.php';

$arquivo = TMP_DIR . '/gabarito_oficial.json';
$gabarito = [];

if (file_exists($arquivo)) {
    $gabarito = json_decode(file_get_contents($arquivo), true) ?: [];
}

resposta_json(['ok' => true, 'gabarito' => $gabarito]);