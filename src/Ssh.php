<?php

namespace Nave\IssSatellite;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Psr\Log\LoggerInterface;

class Ssh
{
    private ?string $connectionName = null;

    private array $sshTunnelConfig;

    public function connection(string $connectionName): self
    {
        $this->connectionName = $connectionName;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function connect(bool $debug = false): void
    {
        $sshConfig = config('iss-satellite.ssh');

        if (! $this->connectionName) {
            $this->getDefaultConnection();
        }

        $this->sshTunnelConfig = $sshConfig[$this->connectionName];

        if (! $sshConfig['host']) {
            $this->log()->error('No host for SSH was provided.');

            return;
        }

        $sshString = [
            'sshpass -p '.$sshConfig['password'],
            'ssh -o "StrictHostKeyChecking no" -f -N -L',
            $this->sshTunnelConfig['tunnel_local_port'].':'.$this->sshTunnelConfig['tunnel'].':'.$this->sshTunnelConfig['tunnel_destination_port'],
            $sshConfig['username'].'@'.$sshConfig['host'],
        ];

        $this->log()->info('Testing SSH connection to: '.$this->connectionName);

        $isConnected = $this->isConnected($debug);

        if (! $isConnected) {
            $this->log()->info('Establishing SSH connection to: '.$this->connectionName);

            Process::run(implode(' ', $sshString))->output();

            $this->log()->info('SSH connection established to: '.$this->connectionName);
        }

        if ($isConnected) {
            $this->log()->info('SSH is already connected to: '.$this->connectionName);
        }
    }

    private function isConnected(bool $debug): bool
    {
        try {
            fsockopen('tcp://localhost', $this->sshTunnelConfig['tunnel_local_port']);

            return true;
        } catch (Exception $exception) {
            if ($debug) {
                throw new Exception($exception);
            }
        }

        return false;
    }

    private function getDefaultConnection(): void
    {
        $sshDefaultConnection = config('iss-satellite.ssh.default_connection');

        $this->connectionName = $sshDefaultConnection;
    }

    public function log(): LoggerInterface
    {
        return Log::channel('stderr');
    }
}
