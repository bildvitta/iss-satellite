<?php

namespace Nave\IssSatellite;

use SoapClient;

class WsCarteira
{
    /**
     * NÃO IMPLEMENTADOS
     * endereco() - Depende de uma classe de cliente específica de la
     * processarEndereco() - Talvez de para implementar, mas é um complemento da endereco()
     * abreviarBairro() - Não vi sentido, parece igual ao abreviarEndereco()
     * siglaPais() - Utiliza dados de países do banco local, talvez de para utilizar o laravel countries do proprio módulo
     * estruturaParcelaVazia() - Faz mais sentido essa estrutura ser feita no próprio módulo pois as parcelas não vazias já serão montadas la
     */

    /**
     * wsCarteira()
     * Faz uma chamada no SoapServer do WS Carteira.
     * Não é necessário passar o login e senha, pois eles são passados automaticamente.
     */
    public static function call(string $call, array $data = []): array
    {
        if (! config('iss-satellite.wscarteira.wsdl')) {
            return [
                'error'   => true,
                'message' => __('WSCarteira WSDL config not found'),
            ];
        }

        $soapClient = new SoapClient(config('iss-satellite.wscarteira.wsdl'), [
            'encoding'       => 'UTF-8',
            'trace'          => 1,
            'exceptions'     => 1,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]),
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);

        $data = array_merge([
            'Login' => config('iss-satellite.wscarteira.login'),
            'Senha' => config('iss-satellite.wscarteira.password'),
        ], $data);

        $result = $soapClient->__soapCall($call, $data);

        if (! $result) {
            return [
                'error'   => true,
                'message' => $soapClient->getError(),
            ];
        }

        if (! is_array($result)) {
            return [
                'error'   => true,
                'message' => __('Unknown error'),
            ];
        }

        $resultArray = array_values($result);

        if ($resultArray[0]['Erro'] === 'true' || $resultArray[0]['Descricao'] !== '') {
            return [
                'error'   => true,
                'message' => "$call: {$resultArray[0]['Descricao']}",
            ];
        }

        if ($resultArray[0]['Erro'] === 'false' || $resultArray[0]['Descricao'] === '') {
            return [
                'error'   => false,
                'message' => "$call: Success",
            ];
        }

        return [
            'error'   => true,
            'message' => 'Erro desconhecido',
        ];
    }

    /**
     * sanitizaDados()
     * Substitui caracteres especiais por caracteres comuns e outros caracteres por underline
     */
    public static function sanitizeString(string $string): string
    {
        if (! $string) {
            return $string;
        }

        $string = trim($string);

        $string = preg_replace('/[áàãâä]/ui', 'a', $string);
        $string = preg_replace('/[éèêë]/ui', 'e', $string);
        $string = preg_replace('/[íìîï]/ui', 'i', $string);
        $string = preg_replace('/[óòõôö]/ui', 'o', $string);
        $string = preg_replace('/[úùûü]/ui', 'u', $string);
        $string = preg_replace('/[ç]/ui', 'c', $string);
        $string = preg_replace('/[^a-z0-9\s]/i', '_', $string);
        $string = preg_replace('/_+/', '_', $string);
        $string = preg_replace('/\s+/', ' ', $string);

        return $string;
    }

    /**
     * abreviar()
     * Abrevia prefixos comuns de um endereço
     */
    public static function abbreviateAddressPrefix(string $address): string
    {
        $abbreviations = [
            'AREA'         => 'A',
            'ACESSO'       => 'AC',
            'AEROPORTO'    => 'AER',
            'ALAMEDA '     => 'AL',
            'APARTAMENTO'  => 'AP',
            'APT'          => 'AP',
            'APTO'         => 'AP',
            'AV.'          => 'AV',
            'AVE'          => 'AV',
            'AVEN'         => 'AV',
            'AVENIDA'      => 'AV',
            'BALNEARIO'    => 'BAL',
            'BECO'         => 'BC',
            'BLOCO'        => 'BL',
            'BOSQUE'       => 'BSQ',
            'CASA'         => 'CS',
            'CALÇADA'      => 'CAL',
            'CANAL'        => 'CAN',
            'CHACARA'      => 'CH',
            'CAMINHO'      => 'CAM',
            'CAMPO'        => 'CPO',
            'CICLOVIA'     => 'CIC',
            'CONDOMINIO'   => 'CD',
            'CONDOMÍNIO'   => 'CD',
            'CONJUNTO'     => 'CJ',
            'COOPERATIVA'  => 'COOP',
            'CORREGO'      => 'CRG',
            'CONTORNO'     => 'CTN',
            'DESVIO'       => 'DSV',
            'DISTRITO'     => 'DT',
            'ESTRADA'      => 'EST',
            'ESTADIO'      => 'ETD',
            'EVANGELICA'   => 'EVAN',
            'FAZENDA'      => 'FAZ',
            'GALERIA'      => 'GAL',
            'GRANJA'       => 'GRJ',
            'HABITACIONAL' => 'HAB',
            'JARDIM'       => 'JD',
            'LAGOA'        => 'LGA',
            'LAGO'         => 'LGO',
            'LOTEAMENTO'   => 'LOT',
            'LOTE'         => 'LT',
            'MARGINAL'     => 'MARG',
            'MERCADO'      => 'MER',
            'MODULO'       => 'MOD',
            'MORRO'        => 'MRO',
            'MONTE'        => 'MTE',
            'NUCLEO'       => 'NUC',
            'PATIO'        => 'PAT',
            'PARQUE'       => 'PRQ',
            'PASSARELA'    => 'PSA',
            'PONTE'        => 'PTE',
            'PC'           => 'PÇ',
            'PC.'          => 'PÇ',
            'PÇ.'          => 'PÇ',
            'PRAÇA'        => 'PÇ',
            'PRACA'        => 'PÇ',
            'PROFESSOR'    => 'PROF',
            'PROFESSORA'   => 'PROFa',
            'QUADRA'       => 'QD',
            'RECANTO'      => 'REC',
            'RESIDENCIAL'  => 'RES',
            'RETA'         => 'RET',
            'ROD'          => 'ROD',
            'ROD.'         => 'ROD',
            'RODOVIA'      => 'ROD',
            'RUA'          => 'R',
            'SALA'         => 'SL',
            'SETOR'        => 'ST',
            'TERMINAL'     => 'TER',
            'TORRE'        => 'TR',
            'TREVO'        => 'TRV',
            'TUNEL'        => 'TUN',
            'VILA'         => 'VL',
            'VALE'         => 'VLE',
        ];

        $words = explode(' ', $address);

        foreach ($words as $key => $word) {
            if (isset($abbreviations[$word])) {
                $words[$key] = $abbreviations[$word];
            }
        }

        return implode(' ', $words);
    }

    /**
     * removerElementosLigacao()
     * Remove preposições e artigos de uma string
     */
    public static function removePrepositions(string $string): string
    {
        $prepositions = [
            ' Da ', ' da ', ' DA ',
            ' De ', ' de ', ' DE ',
            ' Do ', ' do ', ' DO ',
            ' Das ', ' das ', ' DAS ',
            ' Dos ', ' dos ', ' DOS ',
            ' e ', ' E ', ' para ',
        ];

        return str_replace($prepositions, ' ', $string);
    }

    /**
     * abreviarPalavras()
     * Abrevia palavras de um texto
     */
    public static function abbreviateText(string $text, int $maxLenght): string
    {
        $size = strlen($text);
        $words = explode(' ', $text);
        $wordsCount = count($words);
        $i = $wordsCount - 2;

        while ($size > $maxLenght) {
            if ($i < 0) {
                break;
            }
            if (strlen($words[$i]) > 4) {
                $words[$i] = substr($words[$i], 0, 3);
            }
            $i--;
        }

        return implode(' ', $words);
    }

    /**
     * abreviarEndereco()
     * Abrevia um endereço.
     * Primeiro tenta abreviar prefixos comuns.
     * Se o endereço ainda estiver muito grande, remove preposições e artigos.
     * Se o endereço ainda estiver muito grande, abrevia palavras.
     */
    public static function abbreviateAddress(string $address, int $maxLenght): string
    {
        $address = self::sanitizeString($address);
        $address = str_replace(' ', '', $address);
        $address = mb_strtoupper($address);

        if (strlen($address) > $maxLenght) {
            $address = self::abbreviateAddressPrefix($address);
        }

        if (strlen($address) > $maxLenght) {
            $address = self::removePrepositions($address);
        }

        if (strlen($address) > $maxLenght) {
            $address = self::abbreviateText($address, $maxLenght);
        }

        return $address;
    }

    /**
     * clienteEstadoCilvil()
     * Retorna o código de estado civil aceito pelo WS Carteira
     */
    public static function getCivilStatusCode(string $civilStatusName): string
    {
        $civilStatuses = [
            'pessoa Jurídica'                                    => '',
            'solteiro'                                           => 'S',
            'casado com comunhão total APÓS lei nº 6.515/77'     => 'C',
            'casado com comunhão parcial APÓS lei nº 6.515/77'   => 'C',
            'casado com separação de bens APÓS lei nº 6.515/77'  => 'C',
            'união estável'                                      => 'A',
            'separado judicialmente'                             => 'J',
            'divorciado'                                         => 'D',
            'viúvo'                                              => 'V',
            'casado com comunhão total ANTES lei nº 6.515/77'    => 'C',
            'casado com comunhão parcial ANTES lei nº 6.515/77'  => 'C',
            'casado com separação de bens ANTES lei nº 6.515/77' => 'C',
            'casado com separação obrigatória de bens'           => 'C',
            'união estável com separação total de bens'          => 'A',
        ];

        return $civilStatuses[mb_strtolower($civilStatusName)];
    }

    /**
     * eCasado()
     * Retorna se o estado civil é casado
     */
    public static function isMarried(string $civilStatusName): bool
    {
        if (empty($civilStatusName)) {
            return false;
        }

        $marriedCivilStatuses = [
            'casado com comunhão total APÓS lei nº 6.515/77',
            'casado com comunhão parcial APÓS lei nº 6.515/77',
            'casado com separação de bens APÓS lei nº 6.515/77',
            'união estável',
            'casado com comunhão total ANTES lei nº 6.515/77',
            'casado com comunhão parcial ANTES lei nº 6.515/77',
            'casado com separação de bens ANTES lei nº 6.515/77',
            'casado com separação obrigatória de bens',
        ];

        return in_array(mb_strtolower($civilStatusName), $marriedCivilStatuses);
    }

    /**
     * regimeCasamento()
     * Retorna o código de regime de casamento aceito pelo WS Carteira
     */
    public static function getMarriageRegimeCode(string $civilStatusName): string
    {
        if (! self::isMarried($civilStatusName)) {
            return '';
        }

        $civilStatusRegimes = [
            'casado com comunhão total APÓS lei nº 6.515/77'     => 'U',
            'casado com comunhão parcial APÓS lei nº 6.515/77'   => 'P',
            'casado com separação de bens APÓS lei nº 6.515/77'  => 'S',
            'união estável'                                      => 'S',
            'casado com comunhão total ANTES lei nº 6.515/77'    => 'U',
            'casado com comunhão parcial ANTES lei nº 6.515/77'  => 'P',
            'casado com separação de bens ANTES lei nº 6.515/77' => 'S',
            'casado com separação obrigatória de bens'           => 'S',
        ];

        return $civilStatusRegimes[mb_strtolower($civilStatusName)];
    }

    /**
     * getIntervaloMes()
     * Retona o código de periodicidade aceito pelo WS Carteira
     */
    public static function getPeriodicityCode(string $periodicityName): string
    {
        switch ($periodicityName) {
            case 'bimonthly': // BIMESTRAL
                $periodicityCode = 2;
                break;
            case 'quarterly': // TRIMESTRAL
                $periodicityCode = 3;
                break;
            case 'semiannual':// SEMESTRAL
                $periodicityCode = 6;
                break;
            case 'yearly':// ANUAL
                $periodicityCode = 12;
                break;
            default:
                $periodicityCode = 1;
                break;
        }

        return $periodicityCode;
    }
}
