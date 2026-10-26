<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta_json(['ok' => false, 'erro' => 'Método inválido']);
}

$gabarito = [];
for ($i = 1; $i <= 45; $i++) {
    $g = strtoupper(trim($_POST['gab' . $i] ?? ''));
    if (!in_array($g, ['A','B','C','D'])) {
        resposta_json(['ok' => false, 'erro' => 'Preencha todas as 45 questões (A-D).']);
    }
    $gabarito[$i] = $g;
}

$arquivo = TMP_DIR . '/gabarito_oficial.json';
$tmp = $arquivo . '.tmp';
if (file_put_contents($tmp, json_encode($gabarito, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
    resposta_json(['ok' => false, 'erro' => 'Falha ao gravar arquivo.']);
}
rename($tmp, $arquivo);

resposta_json(['ok' => true, 'total' => count($gabarito)]);