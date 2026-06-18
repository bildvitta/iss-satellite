<?php

namespace Nave\IssSatellite;

use Illuminate\Support\Facades\Http;

class Finnet
{
    private static array $keysRequired = [
        'URL',
    ];

    public static function call(array $credentials, array $data = []): array
    {
        $validateCredentials = self::validateCredentials($credentials);
        if ($validateCredentials['error'] === true) {
            return $validateCredentials;
        }

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($credentials['URL'], $data);

        if ($response->failed()) {
            return [
                'error'   => true,
                'message' => $response->body(),
            ];
        }

        return [
            'error'   => false,
            'message' => 'Boleto integrado com sucesso!',
            'data'    => json_decode($response->body(), true),
        ];
    }

    public static function sanitizeJson(string $json): array
    {
        $result = json_decode($json, true);

        $result['dados']['documento_numero'] = str_replace('-', '', $result['dados']['documento_numero']);

        $result['dados']['pagador_endereco_bairro'] = preg_replace('/[^a-zA-Z0-9\s]/', '', $result['dados']['pagador_endereco_bairro']);
        $result['dados']['pagador_endereco_logradouro'] = preg_replace('/[^a-zA-Z0-9\s]/', '', $result['dados']['pagador_endereco_logradouro']);

        $result['dados']['pagador_endereco_cidade'] = substr($result['dados']['pagador_endereco_cidade'], 0, 15);

        return $result;
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
