<?php

declare(strict_types=1);

namespace MapeiaCEP;

use MapeiaCEP\Http\FalhaDeRedeException;
use MapeiaCEP\Http\HttpClient;

/**
 * Cliente da API pública ViaCEP (https://viacep.com.br/).
 */
final readonly class ViaCepClient
{
    public const string URL_BASE = 'https://viacep.com.br/ws/';

    /** Limite de resultados da pesquisa por endereço imposto pela ViaCEP. */
    public const int LIMITE_RESULTADOS = 50;

    public function __construct(private HttpClient $http)
    {
    }

    /**
     * @return Endereco|null null quando o CEP tem formato válido, mas não existe.
     * @throws ViaCepException
     */
    public function buscarPorCep(Cep $cep): ?Endereco
    {
        $dados = $this->requisitar(self::URL_BASE . $cep->digitos . '/json/');

        if (!is_array($dados) || array_is_list($dados)) {
            throw new ViaCepException('Resposta inesperada na consulta por CEP.');
        }

        // A ViaCEP retorna {"erro": "true"} (ou true, em versões antigas) para CEP inexistente.
        if (array_key_exists('erro', $dados) && in_array($dados['erro'], [true, 'true'], true)) {
            return null;
        }

        return Endereco::deArray($dados);
    }

    /**
     * Pesquisa por trechos de cidade e logradouro. Retorna até 50 resultados.
     *
     * @return list<Endereco>
     * @throws ViaCepException
     */
    public function buscarPorEndereco(BuscaPorEndereco $busca): array
    {
        $url = self::URL_BASE
            . rawurlencode($busca->uf) . '/'
            . rawurlencode($busca->cidade) . '/'
            . rawurlencode($busca->logradouro) . '/json/';

        $dados = $this->requisitar($url);

        if (!is_array($dados) || !array_is_list($dados)) {
            throw new ViaCepException('Resposta inesperada na pesquisa por endereço.');
        }

        return array_values(array_map(
            static fn (mixed $item): Endereco => is_array($item)
                ? Endereco::deArray($item)
                : throw new ViaCepException('Item inválido na pesquisa por endereço.'),
            $dados,
        ));
    }

    /**
     * @throws ViaCepException
     */
    private function requisitar(string $url): mixed
    {
        try {
            $resposta = $this->http->get($url);
        } catch (FalhaDeRedeException $e) {
            throw new ViaCepException($e->getMessage(), 0, $e);
        }

        if ($resposta->status === 400) {
            throw new ViaCepException("ViaCEP recusou a requisição (HTTP 400): {$url}");
        }

        if ($resposta->status !== 200) {
            throw new ViaCepException("ViaCEP respondeu HTTP {$resposta->status}: {$url}");
        }

        try {
            return json_decode($resposta->corpo, true, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new ViaCepException("JSON inválido da ViaCEP: {$e->getMessage()}", 0, $e);
        }
    }
}
