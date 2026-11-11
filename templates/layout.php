<?php
/**
 * @var string $titulo
 * @var string $conteudo
 */

use function MapeiaCEP\e;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Consulte endereços pelo CEP ou descubra o CEP a partir do endereço.">
    <title><?= e($titulo) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="topo">
        <a href="/" class="marca">Mapeia<span>CEP</span></a>
    </header>

    <main class="conteudo">
        <?= $conteudo ?>
    </main>

    <footer class="rodape">
        <p>Dados fornecidos por <a href="https://viacep.com.br/" rel="noopener" target="_blank">ViaCEP</a>.</p>
    </footer>

    <script src="/assets/js/app.js" defer></script>
</body>
</html>
