<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Cors.php';

/**
 * Página de diagnóstico — mostra na tela se a origem atual está entre
 * as liberadas no código (src/Cors.php).
 *
 * IMPORTANTE: depois de resolver o problema, apague esse arquivo do
 * servidor (não é seguro deixar uma página de diagnóstico pública
 * rodando pra sempre).
 */

$origensPermitidas = mse_origens_permitidas();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Diagnóstico MSE Academy</title>
<style>
  body{font-family:system-ui,sans-serif; max-width:700px; margin:40px auto; padding:0 20px; line-height:1.6;}
  .caixa{border:2px solid #ccc; border-radius:8px; padding:20px; margin:16px 0;}
  .ok{border-color:#2e7d32; background:#e8f5e9;}
  .erro{border-color:#c62828; background:#ffebee;}
  code{background:#eee; padding:2px 6px; border-radius:4px; font-size:14px;}
  h2{margin-top:0;}
</style>
</head>
<body>
<h1>Diagnóstico da MSE Academy</h1>

<div class="caixa">
  <h2>1. Origens liberadas no código agora (src/Cors.php)</h2>
  <ul>
    <?php foreach ($origensPermitidas as $o): ?>
      <li><code><?= htmlspecialchars($o) ?></code></li>
    <?php endforeach; ?>
  </ul>
</div>

<div class="caixa">
  <h2>2. A origem atual está na lista?</h2>
  <p>Compara direto — sem depender de nenhuma chamada de rede (mais confiável).</p>
  <div id="resultado" style="margin-top:16px;"></div>
</div>

<script>
(function(){
  const div = document.getElementById('resultado');
  const permitidas = <?= json_encode($origensPermitidas) ?>;
  const atual = window.location.origin;

  if(permitidas.includes(atual)){
    div.innerHTML = '<div class="caixa ok"><b>✅ Está na lista!</b><br>Origem atual: <code>' + atual + '</code></div>';
  } else {
    div.innerHTML = '<div class="caixa erro"><b>❌ NÃO está na lista!</b><br>Origem real agora: <code>' + atual + '</code><br>Liberadas no código: <code>' + permitidas.join(', ') + '</code><br><br><b>Solução:</b> adicione <code>' + atual + '</code> na lista dentro de <code>src/Cors.php</code> (função <code>mse_origens_permitidas()</code>) e suba pelo Git.<br><br><i>Atenção: se essa página de diagnóstico foi aberta num endereço diferente de como a Academy normalmente é acessada (ex: você abriu direto, sem passar pelo link do Portal), esse resultado pode não refletir a situação real — abra esse diagnóstico EXATAMENTE do mesmo jeito que abre a Academy normalmente.</i></div>';
  }
})();
</script>

</body>
</html>
