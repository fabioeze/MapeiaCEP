<?php

declare(strict_types=1);

namespace MapeiaCEP\Http;

/**
 * Cliente HTTP baseado em cURL, sempre com timeout e verificação SSL ativa.
 */
final readonly class CurlHttpClient implements HttpClient
{
    public function __construct(
        private int $timeoutConexao = 3,
        private int $timeoutTotal = 8,
        private string $userAgent = 'MapeiaCEP/1.0',
    ) {
    }

    public function get(string $url): RespostaHttp
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => $this->timeoutConexao,
            CURLOPT_TIMEOUT        => $this->timeoutTotal,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
            CURLOPT_USERAGENT      => $this->userAgent,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);

        $corpo = curl_exec($ch);

        if ($corpo === false) {
            throw new FalhaDeRedeException(
                sprintf('Falha ao requisitar %s: [%d] %s', $url, curl_errno($ch), curl_error($ch)),
            );
        }

        return new RespostaHttp((int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE), (string) $corpo);
    }
}
