<?php

namespace Nave\IssSatellite;

use Illuminate\Support\Facades\Http;
use Nave\IssSatellite\Enums\FinnetCallType;

class Finnet
{
    public static function call(array $data = [], FinnetCallType $type = FinnetCallType::DEFAULT): array
    {
        switch ($type) {
            case FinnetCallType::QRCODE:
                $url = config('iss-satellite.finnet.qrcode_url');
                break;
            case FinnetCallType::DEFAULT:
            default:
                $url = config('iss-satellite.finnet.url');
                break;
        }

        if (! $url) {
            return [
                'error'   => true,
                'message' => __('Finnet url config not found'),
            ];
        }

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $data);

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
}
