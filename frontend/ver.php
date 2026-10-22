<?php
$resultado = null;
$arquivoJson = null;
if (isset($_GET['id'])) {
    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id']);
    $arquivoJson = __DIR__ . '/../tmp/' . $id . '.json';
    if (file_exists($arquivoJson)) {
        $resultado = json_decode(file_get_contents($arquivoJson), true);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Resultado</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f4f4f4; color: #222; padding: 20px; }
header { background: #2c3e50; color: #fff; padding: 15px 20px; border-radius: 6px; margin-bottom: 20px; }
main { background: #fff; padding: 20px; border-radius: 6px; max-width: 900px; margin: 0 auto; }
h2 { margin-bottom: 15px; }
.info { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
.info div { background: #ecf0f1; padding: 10px; border-radius: 4px; }
.info strong { display: block; font-size: 12px; color: #555; }
.info span { font-size: 18px; font-weight: bold; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #ddd; padding: 6px 8px; font-size: 13px; text-align: center; }
th { background: #ecf0f1; }
.certo { background: #d4edda; }
.errado { background: #f8d7da; }
.btn { display: inline-block; margin-top: 15px; padding: 10px 20px; background: #27ae60; color: #fff; text-decoration: none; border-radius: 4px; }
.btn:hover { background: #1e8449; }
.erro { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; }
</style>
</head>
<body>
<header><h1>Resultado da Correção</h1></header>
<main>
<?php if (!$resultado): ?>
    <div class="erro">Resultado não encontrado.</div>
<?php else: ?>
    <div class="info">
        <div><strong>Nome</strong><span><?= htmlspecialchars($resultado['nome']) ?></span></div>
        <div><strong>Matrícula</strong><span><?= htmlspecialchars($resultado['matricula']) ?></span></div>
        <div><strong>Acertos</strong><span><?= $resultado['acertos'] ?> / 45</span></div>
        <div><strong>Erros</strong><span><?= $resultado['erros'] ?> / 45</span></div>
        <div><strong>Nota</strong><span><?= number_format($resultado['nota'], 2) ?></span></div>
        <div><strong>Percentual</strong><span><?= number_format($resultado['percentual'], 2) ?>%</span></div>
    </div>

    <h2>Detalhes</h2>
    <table>
        <tr><th>Questão</th><th>Gabarito</th><th>Aluno</th><th>Status</th></tr>
        <?php foreach ($resultado['detalhes'] as $q => $d): ?>
        <tr>
            <td><?= $q ?></td>
            <td><?= htmlspecialchars($d['gabarito']) ?></td>
            <td><?= htmlspecialchars($d['aluno']) ?></td>
            <td class="<?= $d['acertou'] ? 'certo' : 'errado' ?>"><?= $d['acertou'] ? '✓' : '✗' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <a class="btn" href="../backend/baixar_json.php?id=<?= htmlspecialchars($_GET['id']) ?>">Baixar JSON</a>
<?php endif; ?>
</main>
</body>
</html>