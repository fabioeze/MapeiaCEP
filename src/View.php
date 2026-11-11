<?php

declare(strict_types=1);

namespace MapeiaCEP;

/**
 * Renderizador mínimo de templates PHP.
 */
final class View
{
    public function __construct(private readonly string $diretorio)
    {
    }

    /**
     * Renderiza um template dentro do layout padrão.
     *
     * @param array<string, mixed> $dados
     */
    public function renderizar(string $template, array $dados = []): string
    {
        $conteudo = $this->incluir($template, $dados);

        return $this->incluir('layout', $dados + ['conteudo' => $conteudo]);
    }

    /**
     * @param array<string, mixed> $dados
     */
    private function incluir(string $template, array $dados): string
    {
        $arquivo = $this->diretorio . '/' . $template . '.php';

        if (!is_file($arquivo)) {
            throw new \RuntimeException("Template não encontrado: {$template}");
        }

        ob_start();
        try {
            (static function (string $__arquivo, array $__dados): void {
                extract($__dados, EXTR_SKIP);
                require $__arquivo;
            })($arquivo, $dados);
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}
