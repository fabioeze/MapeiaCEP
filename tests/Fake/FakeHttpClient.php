<?php

declare(strict_types=1);

namespace MapeiaCEP\Tests\Fake;

use MapeiaCEP\Http\FalhaDeRedeException;
use MapeiaCEP\Http\HttpClient;
use MapeiaCEP\Http\RespostaHttp;

/**
 * Cliente HTTP falso: devolve uma resposta fixa (ou lança falha de rede) e registra as URLs pedidas.
 */
final class FakeHttpClient implements HttpClient
{
    /** @var list<string> */
    public array $urls = [];

    public function __construct(
        private readonly ?RespostaHttp $resposta = null,
        private readonly bool $falharRede = false,
    ) {
    }

    public static function json(mixed $dados, int $status = 200): self
    {
        return new self(new RespostaHttp($status, json_encode($dados, JSON_THROW_ON_ERROR)));
    }

    public static function bruto(string $corpo, int $status = 200): self
    {
        return new self(new RespostaHttp($status, $corpo));
    }

    public static function semRede(): self
    {
        return new self(falharRede: true);
    }

    public function get(string $url): RespostaHttp
    {
        $this->urls[] = $url;

        if ($this->falharRede || $this->resposta === null) {
            throw new FalhaDeRedeException('Operation timed out');
        }

        return $this->resposta;
    }
}
