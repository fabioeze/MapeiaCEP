# AGENTS.md — MapeiaCEP

Guia para agentes de IA (e pessoas) que trabalham neste repositório.

## Visão geral

**MapeiaCEP** é um site que consulta a API pública do [ViaCEP](https://viacep.com.br/) para:

1. **CEP → Endereço**: o usuário informa um CEP e o site mostra o endereço correspondente.
2. **Endereço → CEP**: o usuário informa UF, cidade e logradouro e o site lista os CEPs encontrados.

O título exibido no site (tag `<title>`, cabeçalho e demais referências) deve ser sempre **MapeiaCEP**.

## Stack e restrições

- **Linguagem principal: PHP** (ambiente atual: PHP 8.4, Composer 2). Toda a lógica de negócio e a chamada à API ficam no lado do servidor, em PHP.
- HTML/CSS para a interface; JavaScript apenas como melhoria progressiva (ex.: máscara de CEP). O site deve funcionar sem JS.
- Sem framework por padrão — PHP puro com uma organização simples. Não adicionar framework ou dependência pesada sem pedido explícito do usuário.
- Dependências via Composer (`composer.json`), com autoload PSR-4. Dependências de desenvolvimento (ex.: PHPUnit) ficam em `require-dev`.
- Requisições HTTP à ViaCEP com cURL ou `file_get_contents` + stream context, sempre com **timeout** definido.
- Idioma da interface, mensagens ao usuário e documentação: **português do Brasil**. Nomes de código (classes, métodos, variáveis) podem ser em inglês ou português, mas mantenha consistência com o que já existir.

## Estrutura de diretórios (proposta)

O repositório está no início. Siga esta estrutura ao criar os arquivos, a menos que o usuário peça outra:

```
MapeiaCEP/
├── public/              # Raiz web (document root) — único diretório exposto
│   ├── index.php        # Ponto de entrada / roteamento simples
│   └── assets/          # CSS, JS, imagens
├── src/                 # Classes PHP (namespace MapeiaCEP\, PSR-4)
│   ├── Http/            # HttpClient (interface), CurlHttpClient, RespostaHttp, FalhaDeRedeException
│   ├── ViaCepClient.php # Chamadas à ViaCEP (recebe um HttpClient injetado)
│   ├── Cep.php          # Normalização/validação do CEP (8 dígitos)
│   ├── BuscaPorEndereco.php # Validação de UF/cidade/logradouro (trechos parciais, mín. 3)
│   ├── Endereco.php     # Objeto de valor readonly
│   ├── HomeController.php # Lê $_GET, chama o cliente e prepara dados para o template
│   ├── View.php, Uf.php, functions.php (e() para escapar HTML)
│   └── *Exception.php   # EntradaInvalidaException (mensagem exibível), ViaCepException (só log)
├── templates/           # Views/partials PHP (apenas apresentação)
├── tests/               # Testes PHPUnit
├── composer.json
├── AGENTS.md
├── CLAUDE.md
└── README.md
```

Princípios:
- `public/` é o único diretório servido; nada de `src/` ou `vendor/` acessível pela web.
- Separar responsabilidades: cliente da API (`src/`), validação de entrada, e apresentação (`templates/`).
- O cliente da ViaCEP deve ser injetável/substituível para permitir testes sem acesso à rede.

## Comandos

```bash
composer install                          # instala dependências
php -S localhost:8000 -t public           # servidor de desenvolvimento
vendor/bin/phpunit                        # testes (após configurar PHPUnit)
php -l caminho/arquivo.php                # checagem de sintaxe
```

Scripts do Composer:

```bash
composer serve   # = php -S localhost:8000 -t public (sem timeout)
composer test    # = vendor/bin/phpunit
composer lint    # php -l em src/, public/, templates/ e tests/
```

Atualize esta seção sempre que adicionar novos scripts (ex.: formatter).

## Ambiente de desenvolvimento

- O `.devcontainer/` usa a imagem `mcr.microsoft.com/devcontainers/universal:6` (PHP 8.4, Composer, Node).
- `.devcontainer/post-create.sh` instala o **Google Chrome** estável, registra o MCP **chrome-devtools** (escopo do usuário, headless, perfil isolado) e roda `composer install` se houver `composer.json`. O script é idempotente; rode-o de novo se algo faltar.
- Para validar a interface, suba `php -S localhost:8000 -t public` e use as ferramentas do MCP `chrome-devtools` (navegar, capturar tela, ler console/rede).

## API ViaCEP — referência

Base: `https://viacep.com.br/ws/`. Gratuita, sem autenticação. Use sempre o formato **JSON**.

### Consulta por CEP

```
GET https://viacep.com.br/ws/{cep}/json/
```

- `{cep}`: exatamente **8 dígitos** numéricos (remova hífen, pontos e espaços antes de enviar).
- Formato inválido (ex.: `950100100`, `95010A10`) → **HTTP 400**.
- Formato válido mas inexistente (ex.: `99999999`) → HTTP 200 com corpo `{"erro": "true"}` (versões antigas retornavam `true` booleano — trate ambos).

Exemplo de resposta (`01001000`):

```json
{
  "cep": "01001-000",
  "logradouro": "Praça da Sé",
  "complemento": "lado ímpar",
  "unidade": "",
  "bairro": "Sé",
  "localidade": "São Paulo",
  "uf": "SP",
  "estado": "São Paulo",
  "regiao": "Sudeste",
  "ibge": "3550308",
  "gia": "1004",
  "ddd": "11",
  "siafi": "7107"
}
```

### Pesquisa por endereço

```
GET https://viacep.com.br/ws/{UF}/{Cidade}/{Logradouro}/json/
```

- `{UF}`: sigla com 2 letras (ex.: `RS`).
- `{Cidade}` e `{Logradouro}`: **mínimo de 3 caracteres** cada. Codifique com `rawurlencode()` (acentos e espaços).
- Cidade e logradouro aceitam **trechos parciais** (ex.: `RS/Alegre/Domingos` retorna ruas de Porto Alegre e de Alegrete). O site deve listar **todas** as opções retornadas.
- Retorna um **array** com até **50** resultados, ordenados por proximidade do logradouro informado. Ao atingir 50, avise o usuário para refinar a busca.
- Nenhum resultado → array vazio `[]`.
- Parâmetros fora das regras → **HTTP 400**.

Exemplo: `https://viacep.com.br/ws/RS/Porto%20Alegre/Domingos%20Jose/json/`

### Boas práticas com a API

- **Valide a entrada antes de chamar a API** (8 dígitos para CEP; UF válida e mínimo de 3 caracteres para cidade/logradouro) e mostre mensagens claras ao usuário.
- Trate todos os casos: HTTP 400, `erro: true`, array vazio, timeout/falha de rede e JSON inválido — nunca exiba erro bruto do PHP ao usuário.
- Não faça requisições em massa nem varreduras; a ViaCEP bloqueia uso abusivo. Considere cache simples das respostas se fizer sentido.
- A lista de UFs válidas é fixa (27 unidades federativas) — mantenha-a em código, não consulte externamente.

## Segurança

- **Escape toda saída** em HTML com `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')` — inclusive dados vindos da ViaCEP e o que o usuário digitou.
- Nunca concatene entrada do usuário em URLs sem sanitizar/validar e codificar.
- Não exiba stack traces ou mensagens internas em produção (`display_errors` desligado; registre em log).
- Mantenha HTTPS nas chamadas à ViaCEP (não desabilite verificação SSL).

## Convenções de código

- PHP 8.4 com `declare(strict_types=1);` em todos os arquivos de `src/`.
- Siga **PSR-12** para estilo e **PSR-4** para autoload.
- Use tipos em parâmetros, retornos e propriedades; prefira classes `readonly` / objetos de valor para os dados de endereço.
- Funções pequenas e com responsabilidade única; sem lógica de negócio dentro dos templates.

## Testes

- Testes com **PHPUnit** em `tests/`.
- Não dependa da API real nos testes unitários: use `tests/Fake/FakeHttpClient` (respostas fixas, falha de rede simulada).
- Cubra: normalização/validação de CEP, validação dos campos de endereço, tratamento de `erro`, array vazio, HTTP 400 e falha de rede.

## Fluxo de trabalho para agentes

- Leia este arquivo antes de começar e mantenha-o atualizado quando a estrutura, comandos ou convenções mudarem.
- Faça mudanças pequenas e focadas; rode `php -l` e os testes antes de concluir.
- Commits e pushes apenas quando o usuário pedir. Mensagens de commit curtas e descritivas.
- Não versione `vendor/`, arquivos de cache ou credenciais (mantenha um `.gitignore` adequado).
