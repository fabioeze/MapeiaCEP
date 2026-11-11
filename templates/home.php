<?php
/**
 * @var string $modo 'cep' | 'endereco'
 * @var array<string, string> $ufs
 * @var array<string, string> $valores
 * @var bool $buscou
 * @var string|null $erro
 * @var \MapeiaCEP\Endereco|null $endereco
 * @var list<\MapeiaCEP\Endereco> $resultados
 * @var int $limite
 */

use function MapeiaCEP\e;

$total = count($resultados);

?>
<section class="intro">
    <h1>Encontre endereços e CEPs</h1>
    <p>Digite um CEP para ver o endereço, ou informe o endereço para descobrir o CEP.</p>
</section>

<nav class="abas" aria-label="Tipo de consulta">
    <a href="/?modo=cep" class="aba<?= $modo === 'cep' ? ' ativa' : '' ?>"
       <?= $modo === 'cep' ? 'aria-current="page"' : '' ?>>Por CEP</a>
    <a href="/?modo=endereco" class="aba<?= $modo === 'endereco' ? ' ativa' : '' ?>"
       <?= $modo === 'endereco' ? 'aria-current="page"' : '' ?>>Por endereço</a>
</nav>

<?php if ($modo === 'cep') : ?>
    <form class="cartao" method="get" action="/">
        <input type="hidden" name="modo" value="cep">

        <label for="cep">CEP</label>
        <div class="linha">
            <input type="text" id="cep" name="cep" value="<?= e($valores['cep']) ?>"
                   placeholder="00000-000" inputmode="numeric" autocomplete="postal-code"
                   maxlength="10" required <?= $buscou ? '' : 'autofocus' ?>
                   title="Informe os 8 dígitos do CEP">
            <button type="submit">Buscar</button>
        </div>
        <p class="dica">Ex.: 01001-000</p>
    </form>
<?php else : ?>
    <form class="cartao" method="get" action="/">
        <input type="hidden" name="modo" value="endereco">

        <div class="grade">
            <div class="campo campo-uf">
                <label for="uf">UF</label>
                <select id="uf" name="uf" required>
                    <option value="">--</option>
                    <?php foreach ($ufs as $sigla => $nome) : ?>
                        <option value="<?= e($sigla) ?>" title="<?= e($nome) ?>"
                            <?= $valores['uf'] === $sigla ? 'selected' : '' ?>><?= e($sigla) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo campo-cidade">
                <label for="cidade">Cidade</label>
                <input type="text" id="cidade" name="cidade" value="<?= e($valores['cidade']) ?>"
                       placeholder="Ex.: Porto" minlength="3" required autocomplete="address-level2">
            </div>

            <div class="campo campo-logradouro">
                <label for="logradouro">Logradouro</label>
                <input type="text" id="logradouro" name="logradouro" value="<?= e($valores['logradouro']) ?>"
                       placeholder="Ex.: Domingos" minlength="3" required autocomplete="address-line1">
            </div>
        </div>

        <button type="submit" class="largo">Buscar CEPs</button>
        <p class="dica">Pode digitar só um trecho do nome (mínimo de 3 caracteres em cidade e logradouro).</p>
    </form>
<?php endif; ?>

<?php if ($erro !== null) : ?>
    <p class="aviso aviso-erro" role="alert"><?= e($erro) ?></p>
<?php elseif ($buscou && $modo === 'cep') : ?>
    <?php if ($endereco === null) : ?>
        <p class="aviso" role="status">CEP não encontrado. Confira os números e tente novamente.</p>
    <?php else : ?>
        <section class="cartao resultado" aria-live="polite">
            <p class="resultado-cep"><?= e($endereco->cep) ?></p>
            <dl class="detalhes">
                <?php if ($endereco->logradouro !== '') : ?>
                    <dt>Logradouro</dt>
                    <dd><?= e($endereco->logradouro) ?></dd>
                <?php endif; ?>
                <?php if ($endereco->complemento !== '') : ?>
                    <dt>Complemento</dt>
                    <dd><?= e($endereco->complemento) ?></dd>
                <?php endif; ?>
                <?php if ($endereco->bairro !== '') : ?>
                    <dt>Bairro</dt>
                    <dd><?= e($endereco->bairro) ?></dd>
                <?php endif; ?>
                <dt>Cidade</dt>
                <dd><?= e($endereco->localidade) ?> – <?= e($endereco->uf) ?></dd>
                <?php if ($endereco->ddd !== '') : ?>
                    <dt>DDD</dt>
                    <dd><?= e($endereco->ddd) ?></dd>
                <?php endif; ?>
            </dl>
        </section>
    <?php endif; ?>
<?php elseif ($buscou && $modo === 'endereco') : ?>
    <?php if ($total === 0) : ?>
        <p class="aviso" role="status">Nenhum CEP encontrado. Tente outro trecho do nome da cidade ou do logradouro.</p>
    <?php else : ?>
        <section class="resultados" aria-live="polite">
            <h2><?= $total ?> <?= $total === 1 ? 'CEP encontrado' : 'CEPs encontrados' ?></h2>
            <?php if ($total >= $limite) : ?>
                <p class="aviso">A ViaCEP retorna no máximo <?= $limite ?> resultados. Detalhe mais o logradouro para refinar a busca.</p>
            <?php endif; ?>
            <ul class="lista">
                <?php foreach ($resultados as $item) : ?>
                    <li class="item">
                        <a class="item-cep" href="/?modo=cep&amp;cep=<?= e(rawurlencode($item->cep)) ?>"><?= e($item->cep) ?></a>
                        <div class="item-endereco">
                            <strong><?= e($item->logradouro !== '' ? $item->logradouro : '(sem logradouro)') ?></strong>
                            <?php if ($item->complemento !== '') : ?>
                                <span class="complemento"><?= e($item->complemento) ?></span>
                            <?php endif; ?>
                            <span class="local">
                                <?= e(implode(' · ', array_filter([$item->bairro, $item->localidade . ' – ' . $item->uf]))) ?>
                            </span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
<?php endif; ?>
