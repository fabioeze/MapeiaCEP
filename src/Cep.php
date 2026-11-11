<?php

declare(strict_types=1);

namespace MapeiaCEP;

/**
 * CEP normalizado: exatamente 8 dígitos.
 */
final readonly class Cep
{
    private function __construct(public string $digitos)
    {
    }

    /**
     * Aceita "01001-000", "01001000", "01.001-000", com espaços etc.
     *
     * @throws EntradaInvalidaException
     */
    public static function deTexto(string $entrada): self
    {
        $entrada = trim($entrada);

        if ($entrada === '') {
            throw new EntradaInvalidaException('Informe um CEP.');
        }

        if (preg_match('/^[\d\s.\-]+$/', $entrada) !== 1) {
            throw new EntradaInvalidaException('O CEP deve conter apenas números.');
        }

        $digitos = (string) preg_replace('/\D/', '', $entrada);

        if (strlen($digitos) !== 8) {
            throw new EntradaInvalidaException('O CEP deve ter 8 dígitos.');
        }

        return new self($digitos);
    }

    public function formatado(): string
    {
        return substr($this->digitos, 0, 5) . '-' . substr($this->digitos, 5);
    }
}
