<?php

declare(strict_types=1);

namespace MapeiaCEP\Tests;

use MapeiaCEP\Cep;
use MapeiaCEP\EntradaInvalidaException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CepTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function cepsValidos(): iterable
    {
        yield 'só dígitos' => ['01001000'];
        yield 'com hífen' => ['01001-000'];
        yield 'com ponto' => ['01.001-000'];
        yield 'com espaços' => ['  01001 000 '];
    }

    #[DataProvider('cepsValidos')]
    public function testNormalizaParaOitoDigitos(string $entrada): void
    {
        $cep = Cep::deTexto($entrada);

        self::assertSame('01001000', $cep->digitos);
        self::assertSame('01001-000', $cep->formatado());
    }

    /** @return iterable<string, array{string}> */
    public static function cepsInvalidos(): iterable
    {
        yield 'vazio' => [''];
        yield 'curto' => ['0100100'];
        yield 'longo' => ['950100100'];
        yield 'com letra' => ['95010A10'];
        yield 'injeção de caminho' => ['01001000/../'];
    }

    #[DataProvider('cepsInvalidos')]
    public function testRejeitaFormatoInvalido(string $entrada): void
    {
        $this->expectException(EntradaInvalidaException::class);

        Cep::deTexto($entrada);
    }
}
