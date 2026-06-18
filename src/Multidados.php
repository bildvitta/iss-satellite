<?php

namespace Nave\IssSatellite;

use SoapClient;

class Multidados
{
    private static array $keysRequired = [
        'WSDL',
        'USER',
        'PASSWORD',
    ];

    public static function call(array $credentials, string $call, array $data = []): array
    {
        $validateCredentials = self::validateCredentials($credentials);
        if ($validateCredentials['error'] === true) {
            return $validateCredentials;
        }

        $soapClient = new SoapClient($credentials['WSDL'], [
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
            'USUARIO_WS' => $credentials['USER'],
            'SENHA_WS' => $credentials['PASSWORD'],
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

    private static function validateCredentials(array $credentials): array
    {
        $parameterKeys = array_keys($credentials);
        $keysNotPresent = [];

        foreach (self::$keysRequired as $keyRequired) {
            if (! in_array($keyRequired, $parameterKeys)) {
                $keysNotPresent[] = $keyRequired;
            }
        }

        if ($keysNotPresent) {
            $keysString = implode(',', $keysNotPresent);

            return [
                'error'   => true,
                'message' => "The keys [$keysString] must be passed.",
            ];
        }

        return [
            'error'   => false,
            'message' => null,
        ];
    }
}
