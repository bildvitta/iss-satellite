<?php

namespace Nave\IssSatellite;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Psr\Log\LoggerInterface;

class Ssh
{
    private array $keysRequired = [
        'HOST',
        'USERNAME',
        'PASSWORD',
        'TUNNEL',
        'LOCAL_PORT',
        'DESTINATION_PORT',
    ];

    /**
     * @throws Exception
     */
    public function connect(array $sshConfig, bool $debug = false): void
    {
        if (! $this->validateParameters($sshConfig)) {
            $this->log()->error('Invalid SSH configuration');

            return;
        }

        $sshString = [
            "sshpass -p {$sshConfig['PASSWORD']}",
            'ssh -o "StrictHostKeyChecking no" -f -N -L',
            "{$sshConfig['LOCAL_PORT']}:{$sshConfig['TUNNEL']}:{$sshConfig['DESTINATION_PORT']}",
            "{$sshConfig['USERNAME']}@{$sshConfig['HOST']}",
        ];

        $info = "ip: {$sshConfig['TUNNEL']} | local port: {$sshConfig['LOCAL_PORT']} | destination_port: {$sshConfig['DESTINATION_PORT']}";

        $this->log()->info("Testing SSH connection to: $info");

        $isConnected = $this->isConnected($sshConfig['LOCAL_PORT'], $debug);

        if (! $isConnected) {
            $this->log()->info("Establishing SSH connection to: $info");

            Process::run(implode(' ', $sshString))->output();

            $this->log()->info("SSH connection established to: $info");
        }

        if ($isConnected) {
            $this->log()->info("SSH is already connected to: $info");
        }
    }

    private function isConnected(int $localPort, bool $debug): bool
    {
        try {
            fsockopen('tcp://localhost', $localPort);

            return true;
        } catch (Exception $exception) {
            if ($debug) {
                throw new Exception($exception);
            }
        }

        return false;
    }

    public function log(): LoggerInterface
    {
        return Log::channel('stderr');
    }

    private function validateParameters(array $sshConfig): bool
    {
        $parameterKeys = array_keys($sshConfig);
        $keysNotPresent = [];
        $keysWithoutValues = [];

        foreach ($this->keysRequired as $keyRequired) {
            if (! in_array($keyRequired, $parameterKeys)) {
                $keysNotPresent[] = $keyRequired;

                continue;
            }

            if ($sshConfig[$keyRequired] === null) {
                $keysWithoutValues[] = $keyRequired;
            }
        }

        if ($keysNotPresent) {
            $keysString = implode(',', $keysNotPresent);
            $this->log()->error("The keys [$keysString] must be passed.");

            return false;
        }

        if ($keysWithoutValues) {
            $keysString = implode(',', $keysWithoutValues);
            $this->log()->error("The keys [$keysString] must have values.");

            return false;
        }

        return true;
    }
}
