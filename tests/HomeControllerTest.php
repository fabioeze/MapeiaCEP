<?php

declare(strict_types=1);

namespace MapeiaCEP\Tests;

use MapeiaCEP\HomeController;
use MapeiaCEP\Tests\Fake\FakeHttpClient;
use MapeiaCEP\View;
use MapeiaCEP\ViaCepClient;
use PHPUnit\Framework\TestCase;

final class HomeControllerTest extends TestCase
{
    /** @var list<string> */
    private array $logs = [];

    private function controller(FakeHttpClient $http): HomeController
    {
        return new HomeController(new ViaCepClient($http), function (string $mensagem): void {
            $this->logs[] = $mensagem;
        });
    }

    public function testSemBuscaNaoChamaApi(): void
    {
        $http = FakeHttpClient::semRede();

        $dados = $this->controller($http)->tratar(['modo' => 'endereco']);

        self::assertSame([], $http->urls);
        self::assertFalse($dados['buscou']);
        self::assertNull($dados['erro']);
    }

    public function testEntradaInvalidaNaoChamaApiEMostraMensagem(): void
    {
        $http = FakeHttpClient::semRede();

        $dados = $this->controller($http)->tratar(['modo' => 'cep', 'cep' => '123']);

        self::assertSame([], $http->urls);
        self::assertSame('O CEP deve ter 8 dígitos.', $dados['erro']);
    }

    public function testFalhaDaApiMostraMensagemGenerica(): void
    {
        $dados = $this->controller(FakeHttpClient::semRede())->tratar(['modo' => 'cep', 'cep' => '01001000']);

        self::assertSame('Não foi possível consultar a ViaCEP agora. Tente novamente em instantes.', $dados['erro']);
        self::assertStringNotContainsString('timed out', $dados['erro']);
        self::assertCount(1, $this->logs);
        self::assertStringContainsString('timed out', $this->logs[0]);
    }

    public function testParametroNaoTextualEhIgnorado(): void
    {
        $dados = $this->controller(FakeHttpClient::semRede())->tratar(['modo' => 'cep', 'cep' => ['x']]);

        self::assertSame('Informe um CEP.', $dados['erro']);
    }

    public function testPaginaListaTodosOsCepsEscapados(): void
    {
        $itens = [
            ['cep' => '91420-270', 'logradouro' => 'Rua São Domingos', 'bairro' => 'Bom Jesus', 'localidade' => 'Porto Alegre', 'uf' => 'RS'],
            ['cep' => '97544-160', 'logradouro' => '<b>Rua</b>', 'bairro' => '', 'localidade' => 'Alegrete', 'uf' => 'RS'],
        ];
        $query = ['modo' => 'endereco', 'uf' => 'rs', 'cidade' => 'Alegre', 'logradouro' => 'Domingos'];

        $dados = $this->controller(FakeHttpClient::json($itens))->tratar($query);
        $html = (new View(dirname(__DIR__) . '/templates'))->renderizar('home', ['titulo' => 'MapeiaCEP'] + $dados);

        self::assertStringContainsString('2 CEPs encontrados', $html);
        self::assertStringContainsString('91420-270', $html);
        self::assertStringContainsString('97544-160', $html);
        self::assertStringContainsString('&lt;b&gt;Rua&lt;/b&gt;', $html);
        self::assertStringNotContainsString('<b>Rua</b>', $html);
    }
}
