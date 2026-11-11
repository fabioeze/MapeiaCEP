<?php

declare(strict_types=1);

namespace MapeiaCEP;

/**
 * Endereço retornado pela ViaCEP.
 */
final readonly class Endereco
{
    public function __construct(
        public string $cep,
        public string $logradouro,
        public string $complemento,
        public string $bairro,
        public string $localidade,
        public string $uf,
        public string $estado = '',
        public string $ibge = '',
        public string $ddd = '',
    ) {
    }

    /**
     * @param array<mixed> $dados item JSON da ViaCEP
     */
    public static function deArray(array $dados): self
    {
        $texto = static fn (string $chave): string =>
            is_scalar($dados[$chave] ?? null) ? trim((string) $dados[$chave]) : '';

        return new self(
            cep: $texto('cep'),
            logradouro: $texto('logradouro'),
            complemento: $texto('complemento'),
            bairro: $texto('bairro'),
            localidade: $texto('localidade'),
            uf: $texto('uf'),
            estado: $texto('estado'),
            ibge: $texto('ibge'),
            ddd: $texto('ddd'),
        );
    }
}
