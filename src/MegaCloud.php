<?php

namespace Nave\IssSatellite;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class MegaCloud
{
    private function prepareRequest(): PendingRequest
    {
        return Http::baseUrl(Config::get('iss-satellite.mega-cloud.url').Config::get('iss-satellite.mega-cloud.prefix'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->connectTimeout(60)
            ->timeout(60)
            ->retry(3, 300)
            ->throw();
    }

    private function getToken(): ?string
    {
        $cacheKey = 'issMegaCloudToken';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $request = $this->prepareRequest()->post('/Auth/SignIn', [
            'username' => Config::get('iss-satellite.mega-cloud.username'),
            'password' => Config::get('iss-satellite.mega-cloud.password'),
        ])
            ->object();

        if (! property_exists($request, 'accessToken')) {
            return null;
        }

        return Cache::remember($cacheKey, now()->addMinutes(90), function () use ($request) {
            return $request->accessToken;
        });
    }

    public function get(string $url, array $query = []): Response
    {
        return $this->prepareRequest()
            ->withToken($this->getToken())
            ->get($url, $query);
    }

    public function post(string $url, array $data = []): Response
    {
        return $this->prepareRequest()
            ->withToken($this->getToken())
            ->post($url, $data);
    }

    public function put(string $url, array $data = []): Response
    {
        return $this->prepareRequest()
            ->withToken($this->getToken())
            ->put($url, $data);
    }

    public function patch(string $url, array $data = []): Response
    {
        return $this->prepareRequest()
            ->withToken($this->getToken())
            ->patch($url, $data);
    }

    public function delete(string $url, array $data = []): Response
    {
        return $this->prepareRequest()
            ->withToken($this->getToken())
            ->delete($url, $data);
    }

    public function getRealEstateDevelopments(array $query = []): Collection
    {
        return $this->get('/globalestruturas/Empreendimentos', $query)->collect();
    }

    public function getRealEstateDevelopmentBlocks(string $realEstateDevelopmentId): Collection
    {
        return $this->get("/globalestruturas/Empreendimentos/$realEstateDevelopmentId/Blocos")->collect();
    }

    public function getRealEstateDevelopmentUnitsByBlock(string $realEstateDevelopmentId, int|string $blockId): Collection
    {
        return $this->get("/globalestruturas/Empreendimentos/$realEstateDevelopmentId/Blocos/$blockId/Unidades")->collect();
    }

    public function getRealEstateDevelopmentsWithBlocksAndUnits(array $query = []): Collection
    {
        return $this->getRealEstateDevelopments($query)->map(function (array $realEstateDevelopment) {
            return collect([
                'id' => $realEstateDevelopment['id'],
                'codigo' => $realEstateDevelopment['codigo'],
                'codigoFilial' => $realEstateDevelopment['codigoFilial'],
                'nome' => $realEstateDevelopment['nome'],
                'blocks' => $this->getRealEstateDevelopmentBlocks($realEstateDevelopment['id'])->map(function (array $block) use ($realEstateDevelopment) {
                    return collect([
                        'id' => $block['id'],
                        'codigo' => $block['codigo'],
                        'codigoFilial' => $block['codigoFilial'],
                        'nome' => $block['nome'],
                        'units' => $this->getRealEstateDevelopmentUnitsByBlock($realEstateDevelopment['id'], $block['id'])->map(function (array $unit) use ($block, $realEstateDevelopment) {
                            return collect([
                                'id' => $unit['id'],
                                'realEstateDevelopmentId' => $realEstateDevelopment['id'],
                                'blockId' => $block['id'],
                                'codigo' => $unit['codigo'],
                                'codigoExterno' => $unit['codigoExterno'],
                                'nome' => $unit['nome'],
                                'status' => $unit['status'],
                            ]);
                        }),
                    ]);
                }),
            ]);
        });
    }

    public function getAllRealEstateDevelopmentUnits(array $query = []): Collection
    {
        return $this->getRealEstateDevelopmentsWithBlocksAndUnits($query)
            ->pluck('blocks.*.units')
            ->flatten(2);
    }
}
