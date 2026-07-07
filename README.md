# iss-satellite

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bildvitta/iss-satellite.svg?style=flat-square)](https://packagist.org/packages/bildvitta/iss-satellite)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/bildvitta/iss-satellite/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/bildvitta/iss-satellite/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/bildvitta/iss-satellite/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/bildvitta/iss-satellite/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/bildvitta/iss-satellite.svg?style=flat-square)](https://packagist.org/packages/bildvitta/iss-satellite)

This package allows Nave Servers to connect with this external services:
- Mega
- WSCarteira
- Finnet
- SSH

## Requirement: Oracle Instant Client + OCI8 PHP extension
This package requires Oracle Instant Client oci8 PHP extension installed on your server for Mega operations
### php:8.X-fpm Dockerfile
```Dockerfile
ENV ORACLE_HOME=/opt/oracle/instantclient_21_13
ENV LD_LIBRARY_PATH=$ORACLE_HOME
ENV PATH=$ORACLE_HOME:$PATH
RUN mkdir -p /opt/oracle && \
    cd /opt/oracle && \
    wget https://download.oracle.com/otn_software/linux/instantclient/2113000/instantclient-basic-linux.x64-21.13.0.0.0dbru.zip && \
    wget https://download.oracle.com/otn_software/linux/instantclient/2113000/instantclient-sdk-linux.x64-21.13.0.0.0dbru.zip && \
    unzip instantclient-basic-linux.x64-21.13.0.0.0dbru.zip && \
    unzip instantclient-sdk-linux.x64-21.13.0.0.0dbru.zip && \
    echo "$ORACLE_HOME" > /etc/ld.so.conf.d/oracle-instantclient.conf && \
    ldconfig
RUN docker-php-ext-configure oci8 --with-oci8=instantclient,$ORACLE_HOME && \
    docker-php-ext-install oci8
```

## Requirement: Soap PHP extension
This package requires Soap PHP extension installed on your server for WSCarteira operations
### php:8.X-fpm Dockerfile
```Dockerfile
RUN apt-get update && apt-get install -y libxml2-dev \
    && docker-php-ext-install soap
```

## Package Installation

You can install the package via composer:

```bash
composer require bildvitta/iss-satellite
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="iss-satellite-config"
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

### Ssh
```php
use Nave\IssSatellite\Facades\Ssh;

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
#### How to use
```php
use Nave\IssSatellite\Facades\MegaCloud as MegaCloudFacade;
use Nave\IssSatellite\Facades\Ssh;

Ssh::connect($sshConfig);

// O método setConnection() só será necessário caso queira passar outra conexão, do contrário o padrão será puxado da config iss-satellite.mega_cloud.default_connection
MegaCloudFacade::setConnection('bild')->getAllRealEstateDevelopmentUnits([
        'filial' => 103442,
    ])
        ->where('status', 'VENDIDA')
        ->values();
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Nave](https://github.com/bildvitta)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
