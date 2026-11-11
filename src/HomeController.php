<?php

declare(strict_types=1);

namespace MapeiaCEP;

/**
 * Trata a requisição da Home e prepara os dados para o template.
 */
final readonly class HomeController
{
    /** @var \Closure(string): void */
    private \Closure $log;

    /**
     * @param (\Closure(string): void)|null $log destino das mensagens técnicas (padrão: error_log)
     */
    public function __construct(private ViaCepClient $viaCep, ?\Closure $log = null)
    {
        $this->log = $log ?? static function (string $mensagem): void {
            error_log('[MapeiaCEP] ' . $mensagem);
        };
    }

    /**
     * @param array<string, mixed> $query normalmente $_GET
     * @return array<string, mixed> dados para templates/home.php
     */
    public function tratar(array $query): array
    {
        $valor = static fn (string $chave): string =>
            is_string($query[$chave] ?? null) ? $query[$chave] : '';

        $modo = $valor('modo') === 'endereco' ? 'endereco' : 'cep';

        $dados = [
            'modo'      => $modo,
            'ufs'       => Uf::TODAS,
            'valores'   => [
                'cep'        => $valor('cep'),
                'uf'         => strtoupper($valor('uf')),
                'cidade'     => $valor('cidade'),
                'logradouro' => $valor('logradouro'),
            ],
            'buscou'     => false,
            'erro'       => null,
            'endereco'   => null,
            'resultados' => [],
            'limite'     => ViaCepClient::LIMITE_RESULTADOS,
        ];

        $enviado = $modo === 'cep'
            ? array_key_exists('cep', $query)
            : array_key_exists('cidade', $query) || array_key_exists('logradouro', $query);

        if (!$enviado) {
            return $dados;
        }

        try {
            if ($modo === 'cep') {
                $dados['endereco'] = $this->viaCep->buscarPorCep(Cep::deTexto($dados['valores']['cep']));
            } else {
                $busca = BuscaPorEndereco::deEntrada(
                    $dados['valores']['uf'],
                    $dados['valores']['cidade'],
                    $dados['valores']['logradouro'],
                );
                $dados['resultados'] = $this->viaCep->buscarPorEndereco($busca);
            }
            $dados['buscou'] = true;
        } catch (EntradaInvalidaException $e) {
            $dados['erro'] = $e->getMessage();
        } catch (ViaCepException $e) {
            ($this->log)($e->getMessage());
            $dados['erro'] = 'Não foi possível consultar a ViaCEP agora. Tente novamente em instantes.';
        }

        return $dados;
    }
}
