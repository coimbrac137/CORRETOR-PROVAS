<?php
require_once __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dados'])) {
    $recebidos = json_decode($_POST['dados'], true);
    if (is_array($recebidos) && count($recebidos) > 0) {
        if (!is_dir(TMP_DIR)) { @mkdir(TMP_DIR, 0777, true); }
        $novoId = gerar_id();
        file_put_contents(TMP_DIR . '/' . $novoId . '.json', json_encode($recebidos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: ver_turma.php?id=' . $novoId);
        exit;
    }
}

$resultados = null;
$arquivoJson = null;
$idRecebido = $_GET['id'] ?? null;

if ($idRecebido) {
    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $idRecebido);
    $arquivoJson = TMP_DIR . '/' . $id . '.json';
    if (file_exists($arquivoJson)) {
        $resultados = json_decode(file_get_contents($arquivoJson), true);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Resultado da Turma</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f4f4f4; color: #222; padding: 20px; }
header { background: #2c3e50; color: #fff; padding: 15px 20px; border-radius: 6px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;}
main { background: #fff; padding: 20px; border-radius: 6px; max-width: 900px; margin: 0 auto; }
h2 { margin-bottom: 15px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
th, td { border: 1px solid #ddd; padding: 8px; font-size: 14px; text-align: center; }
th { background: #ecf0f1; }
.btn { background: #2980b9; color: #fff; border: none; cursor: pointer; padding: 8px 12px; border-radius: 4px; font-size: 12px; margin-right: 5px; text-decoration: none; display: inline-block;}
.btn:hover { background: #21618c; }
.btn-pdf { background: #e74c3c; }
.btn-pdf:hover { background: #c0392b; }
.detalhes-box { display: none; background: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
.detalhes-box.ativo { display: block; }
.certo { background: #d4edda; }
.errado { background: #f8d7da; }
.btn-voltar { display: inline-block; margin-top: 15px; padding: 10px 20px; background: #7f8c8d; color: #fff; text-decoration: none; border-radius: 4px; }
.btn-voltar:hover { background: #95a5a6; }
.erro { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; }
.erro small { display: block; margin-top: 8px; color: #555; }
.header-actions { display: flex; gap: 10px; }
.resumo-aluno { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 15px; background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
.resumo-aluno div { text-align: center; }
.resumo-aluno strong { display: block; font-size: 12px; color: #555; }
.resumo-aluno span { font-size: 16px; font-weight: bold; }
.nome-aluno { text-align: left; }
@media print {
    @page { margin: 15mm; }
    body { background: #fff; padding: 0; font-size: 12px; }
    header, .btn, .btn-voltar, .nao-imprimir { display: none !important; }
    main { max-width: 100%; padding: 0; box-shadow: none; }
    #tabela-turma, .linha-aluno { display: none !important; }
    .detalhes-box { display: block !important; border: none; background: #fff; padding: 0; page-break-after: always; margin-bottom: 0; }
    .detalhes-box:last-child { page-break-after: auto; }
    .resumo-aluno { border: 1px solid #000; padding: 5px; margin-bottom: 10px; }
    .resumo-aluno strong { color: #000; }
    .certo, .errado { background: transparent !important; font-weight: bold; }
    th, td { border: 1px solid #000 !important; padding: 4px; }
    .imprimir-apenas-este .detalhes-box { display: none !important; }
    .imprimir-apenas-este .detalhes-box.ativo { display: block !important; }
}
</style>
</head>
<body>
<header>
    <h1>Resultado da Turma</h1>
    <div class="header-actions nao-imprimir">
        <button class="btn btn-pdf" onclick="imprimirGeral()">Baixar PDF Geral</button>
    </div>
</header>
<main id="conteudo-principal">
<?php if (!$resultados): ?>
    <div class="erro">
        Resultado não encontrado.
        <small>
            ID recebido: <strong><?= htmlspecialchars($idRecebido ?? '(nenhum)') ?></strong><br>
            Caminho procurado: <strong><?= htmlspecialchars($arquivoJson ?? '(nenhum)') ?></strong>
        </small>
    </div>
<?php else: ?>
    <div id="tabela-turma">
        <table>
            <tr>
                <th>Nome</th>
                <th>Acertos</th>
                <th>Erros</th>
                <th>Nota</th>
                <th class="nao-imprimir">Ação</th>
            </tr>
            <?php foreach ($resultados as $index => $aluno): ?>
            <tr class="linha-aluno">
                <td class="nome-aluno"><?= htmlspecialchars($aluno['nome']) ?></td>
                <td><?= $aluno['acertos'] ?> / 45</td>
                <td><?= $aluno['erros'] ?> / 45</td>
                <td><strong><?= number_format($aluno['nota'], 2) ?></strong></td>
                <td class="nao-imprimir">
                    <button class="btn" onclick="toggleDetalhes(<?= $index ?>)">Ver Respostas</button>
                    <button class="btn btn-pdf" onclick="imprimirAluno(<?= $index ?>)">PDF</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div id="caixas-detalhes">
        <?php foreach ($resultados as $index => $aluno): ?>
        <div id="detalhes-<?= $index ?>" class="detalhes-box">
            <h3 style="margin-bottom: 10px;">Aluno: <?= htmlspecialchars($aluno['nome']) ?></h3>
            <div class="resumo-aluno">
                <div><strong>Acertos</strong><span><?= $aluno['acertos'] ?> / 45</span></div>
                <div><strong>Erros</strong><span><?= $aluno['erros'] ?> / 45</span></div>
                <div><strong>Nota</strong><span><?= number_format($aluno['nota'], 2) ?></span></div>
                <div><strong>Percentual</strong><span><?= number_format($aluno['percentual'], 2) ?>%</span></div>
            </div>
            <table style="margin-top: 0;">
                <tr><th>Questão</th><th>Gabarito</th><th>Aluno</th><th>Status</th></tr>
                <?php foreach ($aluno['detalhes'] as $d): ?>
                <tr>
                    <td><?= (int)($d['questao'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($d['gabarito']) ?></td>
                    <td><?= htmlspecialchars($d['aluno']) ?></td>
                    <td class="<?= !empty($d['acertou']) ? 'certo' : 'errado' ?>"><?= !empty($d['acertou']) ? '✓' : '✗' ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endforeach; ?>
    </div>
    <a class="btn-voltar nao-imprimir" href="index.html">Voltar ao Início</a>
<?php endif; ?>
</main>
<script>
function toggleDetalhes(index) {
    const el = document.getElementById('detalhes-' + index);
    if (el.classList.contains('ativo')) {
        el.classList.remove('ativo');
    } else {
        document.querySelectorAll('.detalhes-box').forEach(box => box.classList.remove('ativo'));
        el.classList.add('ativo');
    }
}
function imprimirGeral() {
    document.body.classList.remove('imprimir-apenas-este');
    document.querySelectorAll('.detalhes-box').forEach(box => box.classList.add('ativo'));
    window.print();
    setTimeout(() => {
        document.querySelectorAll('.detalhes-box').forEach(box => box.classList.remove('ativo'));
    }, 1000);
}
function imprimirAluno(index) {
    document.body.classList.add('imprimir-apenas-este');
    document.querySelectorAll('.detalhes-box').forEach(box => box.classList.remove('ativo'));
    document.getElementById('detalhes-' + index).classList.add('ativo');
    window.print();
    setTimeout(() => {
        document.body.classList.remove('imprimir-apenas-este');
        document.getElementById('detalhes-' + index).classList.remove('ativo');
    }, 1000);
}
</script>
</body>
</html>