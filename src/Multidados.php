<?php

namespace Nave\IssSatellite;

use SoapClient;

class Multidados
{
    public static function call(string $call, array $data = []): array
    {
        if (! config('iss-satellite.multidados.wsdl')) {
            return [
                'error' => true,
                'message' => 'Multidados WSDL config not found',
            ];
        }

        $soapClient = new SoapClient(config('iss-satellite.multidados.wsdl'), [
            'encoding' => 'UTF-8',
            'trace' => 1,
            'exceptions' => 1,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]),
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);

        $data = array_merge([
            'USUARIO_WS' => config('iss-satellite.multidados.username'),
            'SENHA_WS' => config('iss-satellite.multidados.password'),
        ], $data);

        $soapCall = $soapClient->__soapCall($call, $data);
        $result = json_decode($soapCall);

        if ($result->erros ?? false) {
            return [
                'error' => true,
                'message' => $result->erros ?? 'No key erros from Multidados',
            ];
        }

        $success = $result->success ?? false;
        if ($success === true) {
            return [
                'error' => false,
                'message' => $result->idocorrencia,
            ];
        }

        return [
            'error' => true,
            'message' => $soapCall,
        ];
    }
}
