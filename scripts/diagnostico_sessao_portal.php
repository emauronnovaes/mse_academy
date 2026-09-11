<?php
/**
 * ARQUIVO DE DIAGNÓSTICO — não faz parte do site, é só pra descobrir
 * uma informação. Depois de usar, APAGUE esse arquivo do servidor
 * (ele mostra dados sensíveis da sessão).
 *
 * COMO USAR:
 * 1. Peça pra alguém com acesso ao servidor do Portal colocar esse
 *    arquivo em QUALQUER lugar DENTRO do mesmo domínio do Portal
 *    (ex: junto dos outros arquivos .php do Portal).
 * 2. A pessoa TESTANDO precisa primeiro fazer login normal no Portal
 *    (como sempre faz).
 * 3. Sem sair/deslogar, abre esse arquivo no navegador (ex:
 *    portalmse.com.br/diagnostico_sessao.php).
 * 4. Manda o print de tudo que aparecer na tela.
 */

session_start();

echo "<h2>O que existe guardado na sessão agora:</h2>";
echo "<pre>";
if (empty($_SESSION)) {
    echo "(vazio — não tem NADA guardado na sessão do PHP nesse domínio.\n";
    echo "Isso significa que o Portal guarda o login de outro jeito,\n";
    echo "não usando \$_SESSION do PHP.)";
} else {
    print_r($_SESSION);
}
echo "</pre>";

echo "<h2>Nome/domínio do cookie de sessão usado:</h2>";
echo "<pre>";
echo "Nome da sessão: " . session_name() . "\n";
$params = session_get_cookie_params();
echo "Domínio do cookie: " . ($params['domain'] ?: '(vazio = domínio atual)') . "\n";
echo "Caminho do cookie: " . $params['path'] . "\n";
echo "</pre>";
