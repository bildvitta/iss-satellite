# iss-satellite

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bildvitta/iss-satellite.svg?style=flat-square)](https://packagist.org/packages/bildvitta/iss-satellite)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/bildvitta/iss-satellite/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/bildvitta/iss-satellite/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/bildvitta/iss-satellite/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/bildvitta/iss-satellite/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/bildvitta/iss-satellite.svg?style=flat-square)](https://packagist.org/packages/bildvitta/iss-satellite)

This package allows Nave Servers to connect with this external services:
- Mega
- WSCarteiras
- Finnet

## Requirement: OCI8
This package requires Oracle Instant Client oci8 PHP extension installed on your server
### php:8.X-fpm Dockerfile
```Dockerfile
# Install Oracle Instant Client
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

```php
// Mega
$megaConnection = Nave\IssSatellite::megaConnection()->select('select * from EXAMPLE');
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Nave](https://github.com/bildvitta)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
