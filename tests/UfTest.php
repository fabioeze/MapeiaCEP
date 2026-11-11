<?php

declare(strict_types=1);

namespace MapeiaCEP\Tests;

use MapeiaCEP\Uf;
use PHPUnit\Framework\TestCase;

use function MapeiaCEP\e;

final class UfTest extends TestCase
{
    public function testPossuiAs27UnidadesFederativas(): void
    {
        self::assertCount(27, Uf::TODAS);
    }

    public function testValidaSiglaIgnorandoCaixaEEspacos(): void
    {
        self::assertTrue(Uf::isValida('RS'));
        self::assertTrue(Uf::isValida(' sp '));
        self::assertFalse(Uf::isValida('XX'));
        self::assertFalse(Uf::isValida(''));
    }

    public function testEscapaHtml(): void
    {
        self::assertSame('&lt;b&gt;&quot;O&#039;Neil&quot;&lt;/b&gt;', e('<b>"O\'Neil"</b>'));
    }
}
