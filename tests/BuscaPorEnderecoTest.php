<?php

declare(strict_types=1);

namespace MapeiaCEP\Tests;

use MapeiaCEP\BuscaPorEndereco;
use MapeiaCEP\EntradaInvalidaException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BuscaPorEnderecoTest extends TestCase
{
    public function testAceitaTrechosParciaisENormaliza(): void
    {
        $busca = BuscaPorEndereco::deEntrada(' rs ', '  Por ', ' São   Jo ');

        self::assertSame('RS', $busca->uf);
        self::assertSame('Por', $busca->cidade);
        self::assertSame('São Jo', $busca->logradouro);
    }

    public function testContaCaracteresEmVezDeBytes(): void
    {
        // "Sé" tem 3 bytes em UTF-8, mas só 2 caracteres.
        $this->expectException(EntradaInvalidaException::class);

        BuscaPorEndereco::deEntrada('SP', 'São', 'Sé');
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function entradasInvalidas(): iterable
    {
        yield 'UF inexistente' => ['XX', 'Porto', 'Domingos'];
        yield 'UF vazia' => ['', 'Porto', 'Domingos'];
        yield 'cidade curta' => ['RS', 'Po', 'Domingos'];
        yield 'logradouro curto' => ['RS', 'Porto', 'Do'];
        yield 'só espaços' => ['RS', '     ', 'Domingos'];
    }

    #[DataProvider('entradasInvalidas')]
    public function testRejeitaEntradaInvalida(string $uf, string $cidade, string $logradouro): void
    {
        $this->expectException(EntradaInvalidaException::class);

        BuscaPorEndereco::deEntrada($uf, $cidade, $logradouro);
    }
}
