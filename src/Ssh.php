<?php

namespace Nave\IssSatellite;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Psr\Log\LoggerInterface;

class Ssh
{
    private static string $connectionName = 'mega';

    private static array $sshTunnelConfig;

    public static function connection(string $connectionName): self
    {
        self::$connectionName = $connectionName;

        return new self;
    }

    /**
     * @throws Exception
     */
    public static function connect(bool $debug = false): void
    {
        $sshConfig = config('iss-satellite.ssh');
        self::$sshTunnelConfig = $sshConfig[self::$connectionName];

        if (! $sshConfig['host']) {
            self::log()->error('No host for SSH was provided.');

            return;
        }

        $sshString = [
            'sshpass -p '.$sshConfig['password'],
            'ssh -o "StrictHostKeyChecking no" -f -N -L',
            self::$sshTunnelConfig['tunnel_local_port'].':'.self::$sshTunnelConfig['tunnel'].':'.self::$sshTunnelConfig['tunnel_destination_port'],
            $sshConfig['username'].'@'.$sshConfig['host'],
        ];

        self::log()->info('Testing SSH connection to: '.self::$connectionName);

        $isConnected = self::isConnected($debug);

        if (! $isConnected) {
            self::log()->info('Establishing SSH connection to: '.self::$connectionName);

            Process::run(implode(' ', $sshString))->output();

            self::log()->info('SSH connection established to: '.self::$connectionName);
        }

        if ($isConnected) {
            self::log()->info('SSH is already connected to: '.self::$connectionName);
        }
    }

    private static function isConnected(bool $debug): bool
    {
        try {
            fsockopen('tcp://localhost', self::$sshTunnelConfig['tunnel_local_port']);

            return true;
        } catch (Exception $exception) {
            if ($debug) {
                throw new Exception($exception);
            }
        }

        return false;
    }

    public static function log(): LoggerInterface
    {
        return Log::channel('stderr');
    }
}
