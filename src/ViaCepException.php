<?php

declare(strict_types=1);

namespace MapeiaCEP;

/**
 * Falha ao consultar a ViaCEP (rede, HTTP inesperado ou JSON inválido).
 * A mensagem é técnica: registre em log e mostre ao usuário um texto genérico.
 */
final class ViaCepException extends \RuntimeException
{
}
