<?php

namespace Nave\IssSatellite;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Psr\Log\LoggerInterface;

class Ssh
{
    public function __construct(private readonly int $tunnelPort, private readonly string $tunnel, private readonly bool $debug = false)
    {
        //
    }

    public function connect(): void
    {
        $sshConfig = config('iss-satellite.ssh');

        if (! $sshConfig['host']) {
            return;
        }

        if (! $this->isConnected()) {
            $sshString = [
                'sshpass -p '.$sshConfig['password'],
                'ssh -o "StrictHostKeyChecking no" -f -N -L',
                $this->tunnelPort.':'.$this->tunnel.':'.$this->tunnelPort,
                $sshConfig['username'].'@'.$sshConfig['host'],
            ];

            $this->log()->info('Criando SSH tunnel...');

            Process::run(implode(' ', $sshString))->output();

            $this->log()->info('SSH tunnel criado.');
        }
    }

    /*
     * Testa se é possível estabelecer uma conexão com a porta especificada no tunnel
     */
    private function isConnected(): bool
    {
        try {
            fsockopen('tcp://localhost', $this->tunnelPort);

            return true;
        } catch (\Exception $exception) {
            if ($this->debug) {
                $errorException = [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];

                $this->log()->error('fsockopen', $errorException);
            }

            return false;
        }
    }

    private function log(): LoggerInterface
    {
        return Log::channel('stderr');
    }
}
