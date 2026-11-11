<?php

declare(strict_types=1);

namespace MapeiaCEP;

/**
 * Escapa um valor para saída segura em HTML.
 */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
