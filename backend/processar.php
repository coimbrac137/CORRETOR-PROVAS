<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/index.html');
    exit;
}

$modo = $_POST['modo'] ?? 'imagem';
$id = gerar_id();

if ($modo === 'manual') {
    $arquivoGab = TMP_DIR . '/gabarito_oficial.json';
    if (!file_exists($arquivoGab)) {
        die('Nenhum gabarito salvo. Salve o gabarito antes de corrigir.');
    }
    $gabaritoSalvo = json_decode(file_get_contents($arquivoGab), true) ?: [];
    if (count($gabaritoSalvo) !== 45) {
        die('Gabarito salvo incompleto. Salve novamente.');
    }

    if (!isset($_FILES['cartao_aluno']) || $_FILES['cartao_aluno']['error'] !== UPLOAD_ERR_OK) {
        die('Envie a foto do cartão do aluno.');
    }

    $ext = strtolower(pathinfo($_FILES['cartao_aluno']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png'])) {
        die('Formato inválido. Use JPG ou PNG.');
    }

    $imgAluno = TMP_DIR . '/' . $id . '_aluno.' . $ext;
    move_uploaded_file($_FILES['cartao_aluno']['tmp_name'], $imgAluno);

    $script = PYTHON_DIR . '/ler_cartao.py';
    $cmd = escapeshellcmd(PYTHON_BIN) . ' ' . escapeshellarg($script)
         . ' --aluno ' . escapeshellarg($imgAluno)
         . ' --gabarito-json ' . escapeshellarg($arquivoGab)
         . ' --saida ' . escapeshellarg(TMP_DIR . '/' . $id . '.json')
         . ' 2>&1';

    $output = shell_exec($cmd);

    if (!file_exists(TMP_DIR . '/' . $id . '.json')) {
        limpar_arquivo($imgAluno);
        die('<pre>Erro ao processar imagem. Saída Python:' . "\n" . htmlspecialchars($output) . '</pre>');
    }

    limpar_arquivo($imgAluno);
    header('Location: ../frontend/ver.php?id=' . $id);
    exit;
}

// MODO IMAGEM (2 fotos)
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