# bildvitta/iss-satellite

Pacote privado da Nave para integrações Laravel com Mega, Mega Cloud, WSCarteira, Finnet, Multidados e SSH.

## Visão geral

- Nome do pacote: `bildvitta/iss-satellite`
- Namespace principal: `Nave\IssSatellite`
- Publica apenas configuração, sem rotas, views ou migrations por padrão

## Requisitos

- PHP `^8.3`
- Laravel `10`, `11` ou `12`
- `ext-oci8` e Oracle Instant Client para uso do `Mega`
- `ext-soap` para `WsCarteira` e `Multidados`
- Credenciais e endpoints configurados no `.env`

## Acesso a repositórios privados

No projeto cliente, adicione o repositório VCS no `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/ORG/REPO"
    }
  ]
}
```

Depois instale o pacote:

```bash
composer require bildvitta/iss-satellite
```

Autenticação local do Composer com token do GitHub:

```bash
composer config -g github-oauth.github.com <YOUR_TOKEN>
```

No GitHub Actions, configure `COMPOSER_AUTH` antes do `composer install`:

```yaml
env:
  COMPOSER_AUTH: >-
    {"github-oauth":{"github.com":"${{ secrets.COMPOSER_GITHUB_TOKEN }}"}}
```

## Instalação local

No projeto cliente:

1. Adicione o repositório privado no `composer.json`.
2. Instale o pacote com `composer require bildvitta/iss-satellite`.
3. Publique a configuração.
4. Preencha as variáveis de ambiente necessárias.

Publicar configuração:

```bash
php artisan vendor:publish --tag=iss-satellite-config
```

As chaves disponíveis ficam em `config/iss-satellite.php`. Use apenas as integrações que o projeto realmente precisar.

Variáveis mais usadas:

- Mega: `MS_SATELLITE_MEGA_DB_*`
- WSCarteira: `MS_SATELLITE_WSCARTEIRA_*`
- Finnet: `MS_SATELLITE_FINNET_URL` e `MS_SATELLITE_FINNET_URL_QRCODE`
- Multidados: `MS_SATELLITE_MULTDADDOS_*`
- SSH e Mega Cloud: `SSH_*`, `MEGA_TUNNEL*`, `BILD_MEGA_CLOUD_*` e `MEGA_CLOUD_*`

## Comandos úteis

```bash
php artisan vendor:publish --tag=iss-satellite-config
composer analyse
composer test
composer test-coverage
composer format
```

## Convenções do projeto

Classes públicas disponíveis:

```php
use Nave\IssSatellite\Mega;
use Nave\IssSatellite\MegaCloud;
use Nave\IssSatellite\Ssh;
use Nave\IssSatellite\Finnet;
use Nave\IssSatellite\WsCarteira;
use Nave\IssSatellite\Multidados;
```

- `Mega` usa a conexão Oracle configurada em `iss-satellite.mega.db`
- `MegaCloud` usa `default_connection` e autentica por token
- `Ssh` abre túnel SSH para conexões configuradas
- `Finnet`, `WsCarteira` e `Multidados` dependem de configuração válida no `.env`

## Uso básico

```php
use Nave\IssSatellite\Mega;
use Nave\IssSatellite\Facades\MegaCloud;
use Nave\IssSatellite\Facades\Ssh;

$rows = Mega::connection()->select('select * from EXAMPLE');

Ssh::connection('mega')->connect();

$response = MegaCloud::setConnection('bild')->get('/globalestruturas/Empreendimentos');
```

## Informações adicionais

- Consulte `CHANGELOG.md` para histórico de mudanças
- Licença: MIT
