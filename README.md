# MapeiaCEP

Consulta de endereços pelo CEP e de CEPs pelo endereço, usando a API pública do [ViaCEP](https://viacep.com.br/).

## Requisitos

- PHP 8.4 (extensões `curl`, `json`, `mbstring`)
- Composer 2

## Como rodar

```bash
composer install
composer serve        # http://localhost:8000
```

## Testes

```bash
composer test
composer lint
```

Consulte o [AGENTS.md](AGENTS.md) para estrutura, convenções e detalhes da API.
