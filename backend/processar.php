<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/index.html');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$matricula = trim($_POST['matricula'] ?? '');
$modo = $_POST['modo'] ?? 'imagem';

if ($nome === '' || $matricula === '') {
    die('Nome e matrícula são obrigatórios.');
}

$id = gerar_id();

if ($modo === 'manual') {
    $gabarito = [];
    $aluno = [];
    for ($i = 1; $i <= 45; $i++) {
        $g = strtoupper(trim($_POST['gab' . $i] ?? ''));
        $a = strtoupper(trim($_POST['aluno' . $i] ?? ''));
        $gabarito[$i] = in_array($g, ['A','B','C','D','E']) ? $g : '';
        $aluno[$i] = in_array($a, ['A','B','C','D','E']) ? $a : '';
    }

    $acertos = 0;
    $detalhes = [];
    for ($i = 1; $i <= 45; $i++) {
        $acertou = ($gabarito[$i] !== '' && $gabarito[$i] === $aluno[$i]);
        if ($acertou) $acertos++;
        $detalhes[$i] = [
            'gabarito' => $gabarito[$i],
            'aluno' => $aluno[$i],
            'acertou' => $acertou
        ];
    }

    $resultado = [
        'nome' => $nome,
        'matricula' => $matricula,
        'acertos' => $acertos,
        'erros' => 45 - $acertos,
        'nota' => round(($acertos / 45) * 10, 2),
        'percentual' => round(($acertos / 45) * 100, 2),
        'detalhes' => $detalhes
    ];

    file_put_contents(TMP_DIR . '/' . $id . '.json', json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: ../frontend/ver.php?id=' . $id);
    exit;
}

// MODO IMAGEM
if (!isset($_FILES['cartao_aluno']) || !isset($_FILES['cartao_gabarito'])) {
    die('Envie as duas imagens.');
}

if ($_FILES['cartao_aluno']['error'] !== UPLOAD_ERR_OK || $_FILES['cartao_gabarito']['error'] !== UPLOAD_ERR_OK) {
    die('Erro no upload das imagens.');
}

$extAluno = strtolower(pathinfo($_FILES['cartao_aluno']['name'], PATHINFO_EXTENSION));
$extGab = strtolower(pathinfo($_FILES['cartao_gabarito']['name'], PATHINFO_EXTENSION));
$permitidas = ['jpg','jpeg','png'];

if (!in_array($extAluno, $permitidas) || !in_array($extGab, $permitidas)) {
    die('Formato inválido. Use JPG ou PNG.');
}

$imgAluno = TMP_DIR . '/' . $id . '_aluno.' . $extAluno;
$imgGab = TMP_DIR . '/' . $id . '_gabarito.' . $extGab;

move_uploaded_file($_FILES['cartao_aluno']['tmp_name'], $imgAluno);
move_uploaded_file($_FILES['cartao_gabarito']['tmp_name'], $imgGab);

$script = PYTHON_DIR . '/ler_cartao.py';
$cmd = escapeshellcmd(PYTHON_BIN) . ' ' . escapeshellarg($script)
     . ' --aluno ' . escapeshellarg($imgAluno)
     . ' --gabarito ' . escapeshellarg($imgGab)
     . ' --nome ' . escapeshellarg($nome)
     . ' --matricula ' . escapeshellarg($matricula)
     . ' --saida ' . escapeshellarg(TMP_DIR . '/' . $id . '.json')
     . ' 2>&1';

$output = shell_exec($cmd);

if (!file_exists(TMP_DIR . '/' . $id . '.json')) {
    limpar_arquivo($imgAluno);
    limpar_arquivo($imgGab);
    die('<pre>Erro ao processar imagens. Saída Python:' . "\n" . htmlspecialchars($output) . '</pre>');
}

limpar_arquivo($imgAluno);
limpar_arquivo($imgGab);

header('Location: ../frontend/ver.php?id=' . $id);
exit;