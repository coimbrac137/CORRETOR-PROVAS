<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/index.html');
    exit;
}

$modo = $_POST['modo'] ?? 'imagem';
$id = gerar_id();

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
    // Separa por linhas, ignorando linhas vazias
    $linhas = explode("\n", str_replace("\r", "", $texto));
    $linhas = array_filter(array_map('trim', $linhas));

    if (empty($linhas)) {
        die('Nenhum aluno informado.');
    }

    $resultadosTurma = [];
    $id_turma = gerar_id();

    foreach ($linhas as $linha) {
        // Separa o nome do restante (tenta dividir por ":" ou espaço)
        $partes = explode(':', $linha, 2);
        
        if (count($partes) == 2) {
            $nome = trim($partes[0]);
            $respostas_texto = strtoupper(trim($partes[1]));
        } else {
            $nome = 'Aluno ' . (count($resultadosTurma) + 1);
            $respostas_texto = strtoupper(trim($linha));
        }

        // Extrai apenas letras A, B, C, D
        preg_match_all('/[ABCD]/', $respostas_texto, $matches);
        $letras = $matches[0];

        if (count($letras) < 45) {
            continue; // Pula se não tiver 45 respostas
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
            $detalhes[$i] = [
                'gabarito' => $g,
                'aluno' => $a,
                'acertou' => $acertou
            ];
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

    file_put_contents(TMP_DIR . '/' . $id_turma . '.json', json_encode($resultadosTurma, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: ../frontend/ver_turma.php?id=' . $id_turma);
    exit;
}

// ... O restante do código (modo imagem e manual_texto) continua igual ...