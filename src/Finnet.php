<?php

namespace Nave\IssSatellite;

use Illuminate\Support\Facades\Http;

enum FinnetCallType: int
{
    case DEFAULT = 0;
    case QRCODE = 1;
};

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

        return $response->json();
    }
}
