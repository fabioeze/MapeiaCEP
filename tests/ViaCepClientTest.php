<?php

declare(strict_types=1);

namespace MapeiaCEP\Tests;

use MapeiaCEP\BuscaPorEndereco;
use MapeiaCEP\Cep;
use MapeiaCEP\Tests\Fake\FakeHttpClient;
use MapeiaCEP\ViaCepClient;
use MapeiaCEP\ViaCepException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ViaCepClientTest extends TestCase
{
    private const array SE = [
        'cep' => '01001-000',
        'logradouro' => 'Praça da Sé',
        'complemento' => 'lado ímpar',
        'unidade' => '',
        'bairro' => 'Sé',
        'localidade' => 'São Paulo',
        'uf' => 'SP',
        'estado' => 'São Paulo',
        'regiao' => 'Sudeste',
        'ibge' => '3550308',
        'gia' => '1004',
        'ddd' => '11',
        'siafi' => '7107',
    ];

    public function testBuscaPorCepMontaUrlERetornaEndereco(): void
    {
        $http = FakeHttpClient::json(self::SE);

        $endereco = (new ViaCepClient($http))->buscarPorCep(Cep::deTexto('01001-000'));

        self::assertSame(['https://viacep.com.br/ws/01001000/json/'], $http->urls);
        self::assertNotNull($endereco);
        self::assertSame('Praça da Sé', $endereco->logradouro);
        self::assertSame('lado ímpar', $endereco->complemento);
        self::assertSame('São Paulo', $endereco->localidade);
        self::assertSame('11', $endereco->ddd);
    }

    /** @return iterable<string, array{mixed}> */
    public static function respostasDeErro(): iterable
    {
        yield 'erro como string' => ['true'];
        yield 'erro como booleano' => [true];
    }

    #[DataProvider('respostasDeErro')]
    public function testCepInexistenteRetornaNull(mixed $erro): void
    {
        $client = new ViaCepClient(FakeHttpClient::json(['erro' => $erro]));

        self::assertNull($client->buscarPorCep(Cep::deTexto('99999999')));
    }

    public function testBuscaPorEnderecoCodificaParametrosParciais(): void
    {
        $http = FakeHttpClient::json([]);

        (new ViaCepClient($http))->buscarPorEndereco(BuscaPorEndereco::deEntrada('RS', 'Porto', 'São Jo'));

        self::assertSame(['https://viacep.com.br/ws/RS/Porto/S%C3%A3o%20Jo/json/'], $http->urls);
    }

    public function testBuscaPorEnderecoRetornaTodasAsOpcoes(): void
    {
        $itens = [
            ['cep' => '91420-270', 'logradouro' => 'Rua São Domingos', 'bairro' => 'Bom Jesus', 'localidade' => 'Porto Alegre', 'uf' => 'RS'],
            ['cep' => '97544-160', 'logradouro' => 'Rua Domingos Rosso', 'bairro' => 'Centro', 'localidade' => 'Alegrete', 'uf' => 'RS'],
        ];
        $client = new ViaCepClient(FakeHttpClient::json($itens));

        $resultados = $client->buscarPorEndereco(BuscaPorEndereco::deEntrada('RS', 'Alegre', 'Domingos'));

        self::assertCount(2, $resultados);
        self::assertSame('91420-270', $resultados[0]->cep);
        self::assertSame('Alegrete', $resultados[1]->localidade);
        self::assertSame('', $resultados[1]->complemento);
    }

    public function testBuscaPorEnderecoSemResultadosRetornaListaVazia(): void
    {
        $client = new ViaCepClient(FakeHttpClient::json([]));

        self::assertSame([], $client->buscarPorEndereco(BuscaPorEndereco::deEntrada('RS', 'Porto', 'Xyzxyz')));
    }

    /** @return iterable<string, array{FakeHttpClient}> */
    public static function falhas(): iterable
    {
        yield 'HTTP 400' => [FakeHttpClient::bruto('<html>Bad Request</html>', 400)];
        yield 'HTTP 500' => [FakeHttpClient::bruto('', 500)];
        yield 'JSON inválido' => [FakeHttpClient::bruto('{"cep": ')];
        yield 'falha de rede' => [FakeHttpClient::semRede()];
        yield 'formato inesperado' => [FakeHttpClient::json('ok')];
    }

    #[DataProvider('falhas')]
    public function testFalhasNaConsultaPorCepViramViaCepException(FakeHttpClient $http): void
    {
        $this->expectException(ViaCepException::class);

        (new ViaCepClient($http))->buscarPorCep(Cep::deTexto('01001000'));
    }

    #[DataProvider('falhas')]
    public function testFalhasNaBuscaPorEnderecoViramViaCepException(FakeHttpClient $http): void
    {
        $this->expectException(ViaCepException::class);

        (new ViaCepClient($http))->buscarPorEndereco(BuscaPorEndereco::deEntrada('RS', 'Porto', 'Domingos'));
    }

    public function testObjetoNaPesquisaPorEnderecoEhInesperado(): void
    {
        $this->expectException(ViaCepException::class);

        (new ViaCepClient(FakeHttpClient::json(['erro' => 'true'])))
            ->buscarPorEndereco(BuscaPorEndereco::deEntrada('RS', 'Porto', 'Domingos'));
    }
}
