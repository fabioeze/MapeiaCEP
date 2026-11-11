<?php

declare(strict_types=1);

namespace MapeiaCEP;

/**
 * Parâmetros validados para a pesquisa de CEP por endereço.
 * Cidade e logradouro podem ser trechos parciais (mínimo de 3 caracteres).
 */
final readonly class BuscaPorEndereco
{
    public const int MINIMO_CARACTERES = 3;

    private function __construct(
        public string $uf,
        public string $cidade,
        public string $logradouro,
    ) {
    }

    /**
     * @throws EntradaInvalidaException
     */
    public static function deEntrada(string $uf, string $cidade, string $logradouro): self
    {
        $uf = strtoupper(trim($uf));
        $cidade = self::normalizarEspacos($cidade);
        $logradouro = self::normalizarEspacos($logradouro);

        if (!Uf::isValida($uf)) {
            throw new EntradaInvalidaException('Selecione uma UF válida.');
        }

        if (mb_strlen($cidade) < self::MINIMO_CARACTERES) {
            throw new EntradaInvalidaException('Informe ao menos 3 caracteres da cidade.');
        }

        if (mb_strlen($logradouro) < self::MINIMO_CARACTERES) {
            throw new EntradaInvalidaException('Informe ao menos 3 caracteres do logradouro.');
        }

        return new self($uf, $cidade, $logradouro);
    }

    private static function normalizarEspacos(string $texto): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $texto));
    }
}
