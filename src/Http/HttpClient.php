<?php

declare(strict_types=1);

namespace MapeiaCEP\Http;

/**
 * Abstração mínima de cliente HTTP, para permitir testes sem rede.
 */
interface HttpClient
{
    /**
     * @throws FalhaDeRedeException quando não for possível obter resposta (timeout, DNS, SSL...).
     */
    public function get(string $url): RespostaHttp;
}
