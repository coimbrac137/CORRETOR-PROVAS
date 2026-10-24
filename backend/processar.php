<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/index.html');
    exit;
}

$modo = $_POST['modo'] ?? 'imagem';

if ($modo === 'lote') {
    $arquivoGab = TMP_DIR . '/gabarito_oficial.json';
    if (!file_exists($arquivoGab)) {
        die('Nenhum gabarito salvo. Salve o gabarito antes de corrigir.');
    }
    $gabaritoSalvo = json_decode(file_get_contents($arquivoGab), true) ?: [];
    if (count($gabaritoSalvo) !== 45) {
        die('Gabarito salvo incompleto. Salve novamente.');
    }

    $texto = $_POST['lista_alunos'] ?? '';
    $linhas = explode("\n", str_replace("\r", "", $texto));
    $linhas = array_filter(array_map('trim', $linhas));

    if (empty($linhas)) {
        die('Nenhum aluno informado.');
    }

    $resultadosTurma = [];

    foreach ($linhas as $linha) {
        $partes = explode(':', $linha, 2);
        if (count($partes) == 2) {
            $nome = trim($partes[0]);
            $respostas_texto = strtoupper(trim($partes[1]));
        } else {
            $nome = 'Aluno ' . (count($resultadosTurma) + 1);
            $respostas_texto = strtoupper(trim($linha));
        }

        preg_match_all('/[ABCD]/', $respostas_texto, $matches);
        $letras = $matches[0];
        if (count($letras) < 45) {
            continue;
        }

        $aluno = [];
        for ($i = 0; $i < 45; $i++) {
            $aluno[$i + 1] = $letras[$i];
        }

        $acertos = 0;
        $detalhes = [];
        for ($i = 1; $i <= 45; $i++) {
            $g = $gabaritoSalvo[$i] ?? '';
            $a = $aluno[$i] ?? '';
            $acertou = ($g !== '' && $g === $a);
            if ($acertou) $acertos++;
            $detalhes[$i] = ['gabarito' => $g, 'aluno' => $a, 'acertou' => $acertou];
        }

        $resultadosTurma[] = [
            'nome' => $nome,
            'matricula' => '',
            'acertos' => $acertos,
            'erros' => 45 - $acertos,
            'nota' => round(($acertos / 45) * 10, 2),
            'percentual' => round(($acertos / 45) * 100, 2),
            'detalhes' => $detalhes
        ];
    }

    if (empty($resultadosTurma)) {
        die('Nenhuma linha válida foi encontrada. Certifique-se de que cada aluno tem 45 letras (A, B, C ou D).');
    }

    $id_turma = gerar_id();
    $arquivo = TMP_DIR . '/' . $id_turma . '.json';
    $json = json_encode($resultadosTurma, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $ok = file_put_contents($arquivo, $json);

    if ($ok === false || !file_exists($arquivo)) {
        die('Falha ao salvar resultado em: ' . $arquivo);
    }

    header('Location: ../frontend/ver_turma.php?id=' . $id_turma);
    exit;
}

if ($modo === 'imagem') {
    if (!isset($_FILES['cartao_aluno']) || !isset($_FILES['cartao_gabarito'])) {
        die('Envie as duas imagens.');
    }

    $tmpAluno = $_FILES['cartao_aluno']['tmp_name'];
    $tmpGab = $_FILES['cartao_gabarito']['tmp_name'];

    if (!is_uploaded_file($tmpAluno) || !is_uploaded_file($tmpGab)) {
        die('Falha no upload das imagens.');
    }

    $script = __DIR__ . '/../visao_computacional/ler_cartao.py';
    $cmd = 'python3 ' . escapeshellarg($script) . ' ' . escapeshellarg($tmpAluno) . ' ' . escapeshellarg($tmpGab) . ' 2>&1';
    $saida = shell_exec($cmd);

    $dados = json_decode($saida, true);
    if (!$dados || !isset($dados['respostas_aluno']) || !isset($dados['gabarito'])) {
        die('Erro ao processar imagens. Saída: ' . htmlspecialchars($saida));
    }

    $alunoLetras = $dados['respostas_aluno'];
    $gabaritoLetras = $dados['gabarito'];

    $acertos = 0;
    $detalhes = [];
    for ($i = 1; $i <= 45; $i++) {
        $g = $gabaritoLetras[$i - 1] ?? '';
        $a = $alunoLetras[$i - 1] ?? '';
        $acertou = ($g !== '' && $g === $a);
        if ($acertou) $acertos++;
        $detalhes[$i] = ['gabarito' => $g, 'aluno' => $a, 'acertou' => $acertou];
    }

    $resultado = [[
        'nome' => 'Aluno (via imagem)',
        'matricula' => '',
        'acertos' => $acertos,
        'erros' => 45 - $acertos,
        'nota' => round(($acertos / 45) * 10, 2),
        'percentual' => round(($acertos / 45) * 100, 2),
        'detalhes' => $detalhes
    ]];

    $id_turma = gerar_id();
    $arquivo = TMP_DIR . '/' . $id_turma . '.json';
    $json = json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $ok = file_put_contents($arquivo, $json);

    if ($ok === false || !file_exists($arquivo)) {
        die('Falha ao salvar resultado em: ' . $arquivo);
    }

    header('Location: ../frontend/ver_turma.php?id=' . $id_turma);
    exit;
}

die('Modo desconhecido: ' . htmlspecialchars($modo));