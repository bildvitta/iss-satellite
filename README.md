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

## Package Usage
### Mega
```php
// Mega direct DB Connection
$megaCredentials = [
    'host' => $credentials['mega_db']['credentials']['MEGA_DB_HOST'],
    'port' => $credentials['mega_db']['credentials']['MEGA_TUNNEL_LOCAL_PORT'],
    'database' => $credentials['mega_db']['credentials']['MEGA_DB_DATABASE'],
    'username' => $credentials['mega_db']['credentials']['MEGA_DB_USERNAME'],
    'password' => $credentials['mega_db']['credentials']['MEGA_DB_PASSWORD'],
    'connection_name_prefix' => $credentials['mega_db']['credentials']['CONNECTION_NAME_PREFIX'],
];

Mega::setConnectionData($megaCredentials);
$query = Nave\Mega::connection()->select('select * from EXAMPLE');

// Mega specific functions
$data = [
    'cto_in_codigo' => 123,
    'document' => '123.123.123-12',
    'agn_st_nome' => 'João da Silva',
]
$query = Nave\Mega::clientesSac($data);
```

## Instalação local

No projeto cliente:

$sshConfig = [
    'HOST' => '150.47.109.80',
    'USERNAME' => 'user',
    'PASSWORD' => 'password',
    'TUNNEL' => '238.33.98.211',
    'LOCAL_PORT' => 1521,
    'DESTINATION_PORT' => 1521,
];
// Connect 
Ssh::connect($sshConfig);
```

### Mega Cloud
#### Config
```php
'mega_cloud' => [
      'default_connection' => env('MEGA_CLOUD_DEFAULT_CONNECTION', 'bild'),
      'connect_timeout' => env('MEGA_CLOUD_CONNECTION_TIMEOUT', 120),
      'timeout' => env('MEGA_CLOUD_TIMEOUT', 120),

      'bild' => [
          'url' => env('BILD_MEGA_CLOUD_URL', 'http://127.0.0.1:36700'),
          'prefix' => env('BILD_MEGA_CLOUD_URL_PREFIX', '/api'),
          'username' => env('BILD_MEGA_CLOUD_USERNAME', ''),
          'password' => env('BILD_MEGA_CLOUD_PASSWORD', ''),
          'cache_key' => env('BILD_MEGA_CLOUD_CACHE_KEY', 'bildIssMegaCloudToken'),
      ],
      
      // Configuração adicional para outra empresa usando mega cloud
      'xxx' => [
          'url' => env('XXX_MEGA_CLOUD_URL', '127.0.0.1'),
          'prefix' => env('XXX_MEGA_CLOUD_URL_PREFIX', '/api'),
          'username' => env('XXX_MEGA_CLOUD_USERNAME', ''),
          'password' => env('XXX_MEGA_CLOUD_PASSWORD', ''),
          'cache_key' => env('XXX_MEGA_CLOUD_CACHE_KEY', 'terreIssMegaCloudToken'),
      ],
],
```

Ssh::connect($sshConfig);

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
