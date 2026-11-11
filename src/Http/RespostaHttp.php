<?php

declare(strict_types=1);

namespace MapeiaCEP\Http;

final readonly class RespostaHttp
{
    public function __construct(
        public int $status,
        public string $corpo,
    ) {
    }
}
