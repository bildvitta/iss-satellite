<?php

namespace Nave\IssSatellite;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use PDO;

class Mega
{
    /**
     * NÃO IMPLEMENTADOS
     * getVizinhosByCodUnidade() - Não é Mega, manipula dados do banco do SYS
     * contratoByCtoInCodigo() - Alias de buscaDadosUnidade
     * ecommerce() - Utiliza banco SYS
     * getTelefonesByAgnCodigo() - Utiliza ORM rudimentar do Mega que fizeram, ver se vai importar
     * insertTelefones() - Utiliza ORM rudimentar do Mega que fizeram, ver se vai importar
     * sincronizaPermutantes() - Rotina de buscar no Mega por permutantes e inserir no SYS. Utiliza uma query Mega no início mas o restante é SYS
     */
    protected static function connectionConfig(): void
    {
        config([
            'database.connections.iss-satellite-mega' => config('iss-satellite.mega.db'),
        ]);
    }

    public static function connection(): \Illuminate\Database\Connection
    {
        self::connectionConfig();

        return DB::connection('iss-satellite-mega');
    }

    /**
     * Retorna a query das vendas permutantes
     */
    public static function getPermutationSalesQuery(): Builder
    {
        return self::connection()->table('bild.vw_bld_ono_parc_cli_sys_api')
            ->orderBy('cod_contrato', 'desc');
    }

    /**
     * Retorna todo o fluxo de pagamento de uma data base, CPF/CNPJ e empreendimento
     *
     * $data deve conter os seguintes campos:
     * - data_base: Data base no formato 'd/m/Y' (opcional, se não for passado, será utilizado a data atual)
     * - dt_movimento: Data do movimento no formato 'd/m/Y' (opcional, se não for passado, será considerado '0')
     * - est_in_codigo: Código do empreendimento (opcional, se não for passado, será considerado '0')
     * - document: CPF ou CNPJ do cliente (opcional, se não for passado, será considerado '0') passar ele formatado com máscara, ex: 123.456.789-00 ou 12.345.678/0001-00
     */
    public static function getFinancialStatementBaseDate(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $data_base = "to_date(:data_base, 'dd/mm/yyyy') - 1";

        $query = "
            select vbo.org_in_codigo,
                vbo.cto_in_codigo,
                vbo.cto_ch_status,
                vbo.codigo_exporta,
                vbo.documento,
                vbo.cto_dt_cadastro,
                vbo.cto_re_valorcontrato,
                vbo.cto_re_valorcontrato_ori,
                vbo.par_ch_parcela,
                vbo.par_ch_receita,
                vbo.par_in_codigo,
                vbo.par_re_valororiginal,
                vbo.par_dt_vencimento,
                vbo.par_dt_baixa,
                vbo.sequencia,
                vbo.par_ch_status,
                vbo.par_dt_movimento
            from bild.vw_bld_ono_parcela_sys_api vbo,
                (
                    select distinct
                        cpa.org_tab_in_codigo_new  as org_tab_in_codigo,
                        cpa.org_pad_in_codigo_new  as org_pad_in_codigo,
                        cpa.org_in_codigo_new      as org_in_codigo,
                        cpa.org_tau_st_codigo_new  as org_tau_st_codigo,
                        cpa.cto_in_codigo_new      as cto_in_codigo,
                        cpa.par_in_codigo_new      as par_in_codigo,
                        'S'                        as parcela_alterada
                    from
                        bild.a#car_parcela   cpa,
                        bild.adt_ocorrencia  aoc
                    where cpa.ado_in_ocorrencia = aoc.ado_in_ocorrencia
                    and aoc.ado_ch_operacao       in ('I','U')
                    and trunc(aoc.ado_dt_inclusao) = " . $data_base . "
                )  qry_par
            where vbo.org_tab_in_codigo = qry_par.org_tab_in_codigo (+)
            and vbo.org_pad_in_codigo   = qry_par.org_pad_in_codigo (+)
            and vbo.org_in_codigo       = qry_par.org_in_codigo     (+)
            and vbo.org_tau_st_codigo   = qry_par.org_tau_st_codigo (+)
            and vbo.cto_in_codigo       = qry_par.cto_in_codigo     (+)
            and vbo.par_in_codigo       = qry_par.par_in_codigo     (+)
        ";

        if (array_key_exists('data_base', $data)) {
            $data_base = Carbon::parse($data['data_base'])->format('d/m/Y');
        } else {
            $data_base = date('Y-m-d');
            $data_base = Carbon::parse($data_base)->format('d/m/Y');
        }

        $data['data_base'] = $data_base;

        if (array_key_exists('dt_movimento', $data)) {
            $dt_movimento = Carbon::parse($data['dt_movimento'])->format('d/m/Y');
            $data['dt_movimento'] = $dt_movimento;
            $query .= 'and vbo.par_dt_movimento = :dt_movimento ';
        }

        if (array_key_exists('est_in_codigo', $data) && $data['est_in_codigo'] != '0') {
            $query .= 'and vbo.codigo_exporta = :est_in_codigo ';
        }

        if (array_key_exists('document', $data) && ($data['document'] != '0')) {
            $query .= 'and documento = :document ';
        }

        if (
            (array_key_exists('document', $data) && ($data['document'] == '0'))
            && (array_key_exists('est_in_codigo', $data) && ($data['est_in_codigo'] == '0'))
        ) {
            $query .= " and (nvl(qry_par.parcela_alterada, 'N') = 'S' or trunc(vbo.cto_dt_status) = " . $data_base . ')';
        }

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * Função que atualiza os dados do cliente
     *
     * @param  array  $data
     */
    public static function atualizaDadosCliente($data): int
    {
        $query = ' update bild.glo_agentes t
                    set t.agn_st_nome        = :agn_st_nome,
                        t.agn_st_logradouro  = :agn_st_logradouro,
                        t.agn_st_numero      = :agn_st_numero,
                        t.agn_st_complemento = :agn_st_complemento,
                        t.agn_st_bairro      = :agn_st_bairro,
                        t.agn_st_municipio   = :agn_st_municipio,
                        t.agn_st_cep         = :agn_st_cep,
                        t.agn_st_email       = :agn_st_email
                where t.agn_in_codigo     = :agn_in_codigo
                    and t.agn_pad_in_codigo = :agn_pad_in_codigo
                    and t.agn_tab_in_codigo = :agn_tab_in_codigo';

        return self::connection()->update($query, $data);
    }

    public static function getEstadosCivis(): array
    {
        $query = 'select * from bild.glo_estadocivil';

        return self::connection()->select($query);
    }

    /**
     * Função que pega os dados do cliente pelo cpf/cnpj
     */
    public static function clientes($data): array
    {
        $query = "select CTO_IN_CODIGO,
                    AGN_TAB_IN_CODIGO,
                    AGN_PAD_IN_CODIGO,
                    AGN_IN_CODIGO,
                    AGN_ST_NOME,
                    AGN_CH_ESTCIVIL,
                    AGN_CH_ESTCIVIL_EXTENSO,
                    COD_REGIME_CASAMENTO,
                    DESCR_REGIME_CASAMENTO,
                    AGN_DT_NASCIMENTO,
                    AGN_ST_CARGOPROFISS,
                    AGN_ST_EMAIL,
                    TPL_ST_SIGLA,
                    AGN_ST_LOGRADOURO,
                    AGN_ST_NUMERO,
                    AGN_ST_CEP,
                    UF_ST_SIGLA,
                    AGN_ST_MUNICIPIO,
                    AGN_ST_BAIRRO,
                    AGN_ST_COMPLEMENTO,
                    AGN_ST_CPF,
                    RG,
                    CNPJ,
                    AGN_ST_CONJUGE,
                    AGN_ST_CPF_CONJUGE,
                    AGN_ST_RG_CONJUGE,
                    AGN_ST_NACIONALIDADE
            from  bild.vw_bld_bca_cto_cli_api
            WHERE AGN_ST_CPF = ':document'
                    OR CNPJ = ':document'
                    AND rownum = 1";

        return self::connection()->select($query, $data);
    }

    /**
     * Função que pega os dados do cliente pelo cto_in_codigo
     */
    public static function clienteByCtoInCodigo($data): array
    {
        $query = "select CTO_IN_CODIGO,
                    AGN_TAB_IN_CODIGO,
                    AGN_PAD_IN_CODIGO,
                    AGN_IN_CODIGO,
                    AGN_ST_NOME,
                    AGN_CH_ESTCIVIL,
                    AGN_CH_ESTCIVIL_EXTENSO,
                    COD_REGIME_CASAMENTO,
                    DESCR_REGIME_CASAMENTO,
                    AGN_DT_NASCIMENTO,
                    AGN_ST_CARGOPROFISS,
                    AGN_ST_EMAIL,
                    TPL_ST_SIGLA,
                    AGN_ST_LOGRADOURO,
                    AGN_ST_NUMERO,
                    AGN_ST_CEP,
                    UF_ST_SIGLA,
                    AGN_ST_MUNICIPIO,
                    AGN_ST_BAIRRO,
                    AGN_ST_COMPLEMENTO,
                    AGN_ST_CPF,
                    RG,
                    CNPJ,
                    AGN_ST_CONJUGE,
                    AGN_ST_CPF_CONJUGE,
                    AGN_ST_RG_CONJUGE,
                    AGN_ST_NACIONALIDADE
            from  bild.vw_bld_bca_cto_cli_api
            WHERE CTO_IN_CODIGO = ':cto_in_codigo'
                    AND rownum = 1";

        return self::connection()->select($query, $data);
    }

    /**
     * Função que pega os dados do cliente pelo cpf/cnpj ou pelo nome
     */
    public static function clientesSac($data): array
    {
        $query = 'select CTO_IN_CODIGO,
                    AGN_TAB_IN_CODIGO,
                    AGN_PAD_IN_CODIGO,
                    AGN_IN_CODIGO,
                    AGN_ST_NOME,
                    AGN_CH_ESTCIVIL,
                    AGN_CH_ESTCIVIL_EXTENSO,
                    COD_REGIME_CASAMENTO,
                    DESCR_REGIME_CASAMENTO,
                    AGN_DT_NASCIMENTO,
                    AGN_ST_CARGOPROFISS,
                    AGN_ST_EMAIL,
                    TPL_ST_SIGLA,
                    AGN_ST_LOGRADOURO,
                    AGN_ST_NUMERO,
                    AGN_ST_CEP,
                    UF_ST_SIGLA,
                    AGN_ST_MUNICIPIO,
                    AGN_ST_BAIRRO,
                    AGN_ST_COMPLEMENTO,
                    AGN_ST_CPF,
                    RG,
                    CNPJ,
                    AGN_ST_CONJUGE,
                    AGN_ST_CPF_CONJUGE,
                    AGN_ST_RG_CONJUGE,
                    AGN_ST_NACIONALIDADE
            from  bild.vw_bld_bca_cto_cli_api ';
        if (! empty($data['document']) && empty($data['agn_st_nome'])) {
            $query .= "WHERE AGN_ST_CPF = ':document'
                        OR CNPJ = ':document'";
        } elseif (empty($data['document']) && ! empty($data['agn_st_nome'])) {
            $query .= "WHERE AGN_ST_NOME like ':agn_st_nome'";
        } else {
            $query .= "WHERE AGN_ST_CPF = ':document'
                        OR CNPJ = ':document'
                        AND AGN_ST_NOME like ':agn_st_nome'";
        }

        $customers = self::connection()->select($query, $data);

        foreach ($customers as $customer) {
            $customer->unidade = self::getUnitsByCtoInCodigo($customer->cto_in_codigo);
        }

        return $customers;
    }

    /**
     * NÃO TESTADO
     */
    public static function getEntregaDeChaveByCtoInCodigo($data): array
    {
        $query = 'select * from bild.vw_bld_ono_contr_ocor_sys_api
            where cod_contrato = :cto_in_codigo';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function buscaDadosUnidade($data): array
    {
        $query = 'select *
                    from bild.vw_bld_ono_contrato_sys_api
                where cto_in_codigo = :cto_in_codigo';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function contratos($data): array
    {
        $query = 'select *
                from bild.vw_bld_ono_contrato_sys_api
            where agn_in_codigo = :agn_in_codigo';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * Traz os status do boleto de sinal
     */
    public static function statusBoletoSinal($data): array
    {
        $query = 'select t.*
                from bild.alx_viw_bldbolrapido t
                where t.agn_in_codigo = :agn_in_codigo';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * Traz o extrato do cliente, mesma table de extratoFinanceiro, porém essa não traz duplicado
     */
    public static function sacExtratoCliente($data): array
    {
        $query = 'select *
                from bild.vw_bld_ono_flu_cli_rep_sys_api
                where codigo_contrato_mega = :cto_in_codigo';

        return self::connection()->select($query, $data);
    }

    /**
     * Lista todos os empreendimentos
     */
    public static function empreendimentos(): array
    {
        $query = 'select *
                from bild.vw_bld_ono_est_emp_sys_api';

        return self::connection()->select($query);
    }

    /**
     * NÃO TESTADO
     * Mostra todo o fluxo financeiro do contrato
     */
    public static function extratoFinanceiro($data): array
    {
        $data['date'] = date('Y-m-d');
        $$data['seq'] = 1;

        $query = 'begin
            bild.alx_pck_bldapp.fnc_bld_app_parcela (:organizacao
            , :cto_in_codigo
            , :sequencia
            , :data_base);
            end;';

        self::connection()->select($query, $data);

        $query = 'select t.* from bild.alx_clitmpcontratos t
                where t.org_in_codigo = :organizacao and
                t.cto_in_codigo = :cto_in_codigo and t.seq_in_codigo = :sequencia';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * Retorno a lista de boletos com linha digitavel (liberado pelo financeiro)
     */
    public static function boletos($data): array
    {
        $query = 'begin
            bild.alx_pck_bldapp.fnc_bld_app_parcela (:organizacao
            , :cto_in_codigo
            , :sequencia
            , :data_base);
            end;';
        self::connection()->select($query, $data);

        $query = 'select t.* from bild.alx_clitmpcontratos t
                where t.org_in_codigo = :org_in_codigo
                and t.cto_in_codigo = :cto_in_codigo
                and t.seq_in_codigo = :sequencia
                and t.par_st_linhadigitavel is not null';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function usuarios($data): array
    {
        $pResult = '';

        $query = 'begin
            bild.alx_pck_bldallapisharepoint.p_busca_usuario (:pResult
                , :pCod
                , :pNome
                , :pTipo);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function insereUsuario($data): array
    {
        $pResult = '';

        $query = ' begin
            bild.alx_pck_bldallapisharepoint.p_insere_usuario_mega (:plogin
                , :pnome
                , :pemail
                , :pusubase
                , :pcopia_cc
                , :pcopia_proj
                , :pcopia_org
                , :pcopia_mat
                , :pcopia_grupos);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function spes($data): array
    {
        $pResult = '';

        $query = 'begin
            bild.alx_pck_bldallapisharepoint.p_busca_spe (:pResult
                , :pTexto
                , :pCod
                , :pTipo);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function spesUsuarios($data): array
    {
        $pResult = '';

        $query = 'begin
            bild.alx_pck_bldallapisharepoint.p_busca_spe_usuario (:pResult
                    , :pOperacao
                    , :pUsuario);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function perfilUsuario($pUsuario): array
    {
        $query = ' begin
                bild.alx_pck_bldallapisharepoint.p_busca_perfil_usuario (:pResult
                    , :pUsuario);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function inclusaoAlcada($data): array
    {
        $query = ' begin
                bild.alx_pck_bldallapisharepoint.p_busca_spe_inclusao_alcada (:pResult
                    , :pUsuario);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function alcadaUsuario($data): array
    {
        $pResult = '';

        $query = 'begin
            bild.alx_pck_bldallapisharepoint.p_busca_alcada_usuario (:pResult
                , :pUsuario);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function insereSpeUsuario($data): array
    {
        $query = 'begin
                    bild.alx_pck_bldallapisharepoint.p_insere_spe_usuario (:pUsuario
                    , :pOrg_tab
                    , :pOrg_pad
                    , :pFil_Cod
                    , :pOrg_tau
                    , :pPerfil);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function deleteSpeUsuario($data): array
    {
        $query = 'begin
            bild.alx_pck_bldallapisharepoint.p_deleta_spe_usuario (:pUsuario
                , :pFil_Cod);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function atualizaAlcadaUsuario($data): array
    {
        $query = 'begin
                bild.alx_pck_bldallapisharepoint.p_atualiza_alcada_usuario (:pUsuario
                    , :pOrg_tab
                    , :pOrg_pad
                    , :pOrg_cod
                    , :pOrg_tau
                    , :pFil_cod
                    , :pNivel_1
                    , :pNivel_2
                    , :pNivel_3
                    , :pEstouro);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function deletaAlcadaUsuario($data): array
    {
        $query = 'begin
            bild.alx_pck_bldallapisharepoint.p_deleta_alcada_usuario (:pUsuario
                , :pOrg_tab
                , :pOrg_pad
                , :pOrg_cod
                , :pOrg_tau
                , :pFil_cod);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function clientesPorTelefone($data): array
    {
        $query = "select * from bild.vw_bld_ono_cli_contr_sys_api
            where celular =':phone'";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function torresPorEmpreendimento($data): array
    {
        $query = 'select * from bild.dbm_vw_estrutura e where e.emp_codigo = :realEstateDevelopmentId ';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function torresPorUnidade($data): array
    {
        $query = 'select * from bild.dbm_vw_estrutura e
                where e.und_codigo = :unitId ';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function buscaPedidosSuprimentos($data): array
    {
        $query = 'begin
            bild.alx_pck_bldallapisuprimentos.p_busca_pedidos (:pResult
                , :pDocumento
                , :pData_ini);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function buscaContratosSuprimentos($data): array
    {
        $query = 'begin
            bild.alx_pck_bldallapisuprimentos.p_busca_contratos (:pResult
                , :pDocumento
                , :pData_ini);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function fluxoCliente($data): array
    {
        $query = "select *
                from bild.vw_bld_ono_flu_cli_rep_sys_api
            where codigo_unidade_mega = :id_unidade
                and (cpf = ':cpf_cliente' or cnpj = ':cpf_cliente')";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function verificaStatusNF($data): array
    {
        $query = 'begin
            bild.alx_pck_bldallapisuprimentos.p_verifica_nota (:pREtorno
                , :pFil_doc
                , :pFor_doc
                , :pNota
                , :pData_Emissao);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function getDadosTermo($data): array
    {
        $query = 'begin
            bild.alx_pck_bldonointegracao.prc_rec_dados_termo (:p_in_cod_exporta
                , :p_in_tipo_doc
                , :p_in_cpf_cnpj
                , :p_in_termo
                , :p_in_usuario
                , :p_in_computador
                , :p_out_result);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function getDespesaITBI($data): array
    {
        $query = 'begin
                bild.alx_pck_bldonointegracao.prc_rec_despesas_itbi (:p_in_cod_exporta
                    , :p_in_tipo_doc
                    , :p_in_cpf_cnpj
                    , :p_in_termo
                    , :p_in_usuario
                    , :p_in_computador
                    , :p_out_result);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function getFluxoAssociativo($data): array
    {
        $query = 'begin
                bild.alx_pck_bldonointegracao.prc_rec_status_fluxo_assoc (:p_in_cod_exporta
                    , :p_in_tipo_doc
                    , :p_in_cpf_cnpj
                    , :p_in_usuario
                    , :p_in_computador
                    , :p_out_result);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function atualizaFluxoAssociativo($data): array
    {
        $query = 'begin
            bild.alx_pck_bldonointegracao.prc_atualiz_status_fluxo_assoc (:p_in_cod_exporta
                , :p_in_tipo_doc
                , :p_in_cpf_cnpj
                , :p_in_status
                , :p_in_usuario
                , :p_in_computador
                , :p_out_result);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function atualizaDataBancoEscFluxoAssociativo($data): array
    {
        $query = 'begin
            bild.alx_pck_bldonointegracao.prc_atualiz_datas_fluxo_assoc (:p_in_cod_exporta
                , :p_in_tipo_doc
                , :p_in_cpf_cnpj
                , :p_in_tipo
                , :p_in_data
                , :p_in_usuario
                , :p_in_computador
                , :p_out_result);
                end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function consultaRM($data): array
    {
        $query = "select *
            from bild.vw_bld_ono_req_mat_sys_api
            where filial = :cod_filial
            and trunc(dt_requisicao) between to_date(':data_inicio', 'DD-MM-YYYY')
            and to_date(':data_fim', 'DD-MM-YYYY')";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function consultaCVV($data): array
    {
        $query = "select *
            from bild.vw_bld_ono_nota_ap_sys_api
            where dataentrada >= to_date(':data_inicio', 'DD-MM-YYYY')
            and dataentrada <= to_date(':data_fim', 'DD-MM-YYYY')
            and agn_in_codigo = :cod_filial";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function getdataAssembleia($data): array
    {
        $query = "select est.fil_in_codigo
                , est.est_st_nome
                , est.est_dt_instcondominio
            from bild.dbm_estrutura  est
            where est.est_ch_tipoestrutura = 'E'
            and est.fil_in_codigo = :fil_in_codigo";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function addContaBancariaCliente($data): array
    {
        $query = 'begin
            bild.alx_pck_bldbcaintegracaosys.prc_bld_bca_insere_cc_cli (
                p_documento     => :p_documento     -- CPF ou CPNJ do Cliente;
                , p_est_in_codigo => :p_est_in_codigo -- Código exporta da unidade;
                , p_ban_in_numero => :p_ban_in_numero -- Número do banco;
                , p_age_st_numero => :p_age_st_numero -- Número da agencia;
                , p_contacorr     => :p_contacorr     -- Número da conta;
                , p_tipoconta     => :p_tipoconta     -- C ou P;
                , p_titular       => :p_titular
                , p_cpftitular    => :p_cpftitular
                , p_cnpjtitular   => :p_cnpjtitular);
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function getContaBancariaCliente($data): array
    {
        $query = 'begin
            bild.alx_pck_bldbcaintegracaosys.prc_bld_bca_rec_cc_cli (p_out_result    => :p_out_result
                , p_documento     => :p_documento -- CPF ou CPNJ do Cliente;
                , p_est_in_codigo => :p_est_in_codigo); -- Código exporta da unidade;
            end;';

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     */
    public static function listarParcelasGeradasMegaFinnet($id_venda = null, $num_parcela = null, $base64 = false, $PROP_IN_SEQUENCIA = null, $api = null): array
    {
        /* MOSTRAR E NÃO MOSTRAR A COLUNA DO BASE 64 */
        $coluna_base64 = ($base64) ? 'P.BOLETOBASE64, ' : null;

        $sql_Where = $api ? 'WHERE P.STATUS >= 0' : 'WHERE P.STATUS <> 80';
        $sql_Where .= ($id_venda) && empty($PROP_IN_SEQUENCIA) ? " AND P.PROP_IN_CODIGO    =  {$id_venda}           " : null;
        $sql_Where .= ($num_parcela) && empty($PROP_IN_SEQUENCIA) ? " AND P.PROP_IN_PARCELA   =  {$num_parcela}        " : null;
        $sql_Where .= ($PROP_IN_SEQUENCIA) ? " AND P.PROP_IN_SEQUENCIA =  {$PROP_IN_SEQUENCIA}  " : null;

        $query = "SELECT
                    {$coluna_base64}
                    P.PROP_IN_CODIGO,
                    P.DOCUMENTO,
                    P.EST_IN_CODIGO,
                    P.PROP_VENCTO,
                    P.VLR_PARCELA,
                    P.NOSSONUMERO,
                    P.VALOR_BAIXA,
                    P.DATA_BAIXA,
                    P.STATUS,
                    P.JSONPAR,
                    P.PROP_IN_PARCELA,
                    P.RET_STATUS,
                    P.RET_MENSAGEM,
                    TO_CHAR(P.DATA_BAIXA, 'YYYY-MM-DD') AS DATA_BAIXA_FORMATO_EN,
                    TO_CHAR(P.DATA_CREDITO, 'YYYY-MM-DD') AS DATA_CREDITO_FORMATO_EN,
                    TO_CHAR(P.PROP_VENCTO,'YYYY-MM-DD') AS PROP_VENCTO_FORMATO_EN,
                    P.PROP_IN_SEQUENCIA,
                    P.VLR_PRESTAMISTA,
                    P.AGNCFI_IN_CODIGO,
                    P.AGN_CH_BOLETO
            FROM BILD.CLI_PROPOSTA_SYS  P
            {$sql_Where} ";

        return self::connection()->select($query);
    }

    public static function addPropostaMega(string $cpfCliente, string $codUnidade, string $codProposta, int $codTermo, string $status): void
    {
        self::connection()
            ->table('bild.ALX_CLIINTPROPOSTATERMO')
            ->insert([
                'PROP_CLIENTE' => $cpfCliente,
                'EST_IN_CODIGO' => $codUnidade,
                'PROP_IN_PROP' => $codProposta,
                'TER_IN_CODIGO' => $codTermo,
                'PROP_CH_STATUS' => $status,
                'PROP_DT_IMPORT' => now()->toDateTimeString(),
                'PROP_DT_PROCES' => now()->toDateTimeString(),
            ]);
    }

    public static function addParcelasMega(
        string $cpfCliente,
        string $codUnidade,
        int $numeroParcela,
        string $codProposta,
        string $tipoParcela,
        string $dataVencimento,
        string $valor,
        string $status,
        int $porcentagem,
        string $dataImporta
    ): void {
        self::connection()
            ->table('bild.ALX_CLIINTPROPTERMOPARC')
            ->insert([
                'PROP_CLIENTE' => $cpfCliente,
                'EST_IN_CODIGO' => $codUnidade,
                'PROP_IN_PARC' => $numeroParcela,
                'PROP_IN_PROP' => $codProposta,
                'PROP_CH_PARC' => $tipoParcela,
                'PROP_DT_VENCTO' => DB::raw("TO_DATE('$dataVencimento', 'DD/MM/YYYY')"),
                'PROP_RE_VALOR' => $valor,
                'PROP_CH_STATUS' => $status,
                'PROP_ST_OBSERVACAO' => '',
                'PROP_IN_PERC' => $porcentagem,
                'PROP_DT_IMPORT' => DB::raw("TO_DATE('$dataImporta', 'YYYY-MM-DD HH24:MI:SS')"),
            ]);
    }

    public static function addParcelasCorrecaoMega(
        string $cpfCliente,
        string $codUnidade,
        string $codProposta,
        int $numeroParcela,
        int $indice,
        string $dataVigencia,
        int $defasagem,
        string $reajuste,
        string $juros,
        string $tipoJuro,
        string $vincula,
        string $dataImporta
    ): void {
        self::connection()
            ->table('bild.ALX_CLIINTPROPTERCOR')
            ->insert([
                'PROP_CLIENTE' => $cpfCliente,
                'EST_IN_CODIGO' => $codUnidade,
                'PROP_IN_PROP' => $codProposta,
                'PROP_IN_PARC' => $numeroParcela,
                'PROP_IN_INDICE' => $indice,
                'PROP_DT_VIGEN' => DB::raw("TO_DATE('$dataVigencia', 'DD/MM/YYYY')"),
                'PROP_IN_DEFAS' => $defasagem,
                'PROP_CH_JUROS' => $reajuste,
                'PROP_RE_JUROS' => $juros,
                'PROP_CH_TPJUR' => $tipoJuro,
                'PROP_CH_VINC' => $vincula,
                'PROP_DT_IMPORT' => DB::raw("TO_DATE('$dataImporta', 'YYYY-MM-DD HH24:MI:SS')"),
            ]);
    }

    public static function finalizacaoMega(int $codigoMega, string $dataIntegracao, string $codProposta): void
    {
        self::connection()
            ->table('bild.ALX_CLIINTEGRACAOSYS')
            ->insert([
                'INT_IN_CODIGO' => $codigoMega,
                'INT_DT_DATA' => DB::raw("TO_DATE('$dataIntegracao', 'DD/MM/YYYY HH24:MI:SS')"),
                'INT_IN_DOCTO' => $codProposta,
            ]);
    }

    public static function consultaContrato(int $idUnidade, string $cpfCliente, ?int $codContrato): SupportCollection
    {
        return self::connection()
            ->table('bild.car_contrato as cto')
            ->select([
                'cto.cto_in_codigo as CONTRATO',
                DB::raw("CASE cli.agn_ch_tipopessoafj WHEN 'J' THEN cli.agn_st_cgc ELSE fis.agn_st_cpf END as CPF_CNPJ"),
                DB::raw(
                    "CASE cto.cto_ch_status
                        WHEN 'A' THEN 'Ativo'
                        WHEN 'U' THEN 'Inadimplente'
                        WHEN 'D' THEN 'Distratado'
                        WHEN 'T' THEN 'Transferido'
                        WHEN 'C' THEN 'Cessão de Direitos'
                        WHEN 'Q' THEN 'Quitado'
                    END as STATUS"
                ),
                'env.est_in_codigo as COD_EXPORTA',
                DB::raw("TO_CHAR(oco.oco_dt_cadastro, 'YYYY-MM-DD HH24:MI:SS') as DATA_IMPORTACAO"),
                'emp.est_in_codigo as COD_EMPREENDIMENTO',
                'emp.est_st_nome as EMPREENDIMENTO',
                'blo.est_in_codigo as COD_BLOCO',
                'blo.est_st_nome as BLOCO',
                'uni.est_st_codigo as COD_ST_UNIDADE',
                'uni.est_st_nome as UNIDADE',
                'oco.oco_dt_cadastro',
            ])
            ->join('bild.car_contrato_envolvido as env', function (JoinClause $join) {
                $join->on('cto.cto_in_codigo', '=', 'env.cto_in_codigo')
                    ->on('cto.org_tab_in_codigo', '=', 'env.org_tab_in_codigo')
                    ->on('cto.org_pad_in_codigo', '=', 'env.org_pad_in_codigo')
                    ->on('cto.org_in_codigo', '=', 'env.org_in_codigo')
                    ->on('cto.org_tau_st_codigo', '=', 'env.org_tau_st_codigo');
            })
            ->join('bild.dbm_unidade as un1', function (JoinClause $join) {
                $join->on('env.est_in_codigo', '=', 'un1.est_in_codigo')
                    ->on('env.org_in_codigo', '=', 'un1.org_in_codigo')
                    ->on('env.org_tau_st_codigo', '=', 'un1.org_tau_st_codigo')
                    ->on('env.org_pad_in_codigo', '=', 'un1.org_pad_in_codigo')
                    ->on('env.org_tab_in_codigo', '=', 'un1.org_tab_in_codigo');
            })
            ->join('bild.dbm_estrutura as emp', function (JoinClause $join) {
                $join->on('un1.emp_est_in_codigo', '=', 'emp.est_in_codigo')
                    ->on('un1.org_in_codigo', '=', 'emp.org_in_codigo')
                    ->on('un1.org_tau_st_codigo', '=', 'emp.org_tau_st_codigo')
                    ->on('un1.org_pad_in_codigo', '=', 'emp.org_pad_in_codigo')
                    ->on('un1.org_tab_in_codigo', '=', 'emp.org_tab_in_codigo');
            })
            ->join('bild.dbm_estrutura as blo', function (JoinClause $join) {
                $join->on('un1.blo_est_in_codigo', '=', 'blo.est_in_codigo')
                    ->on('un1.org_in_codigo', '=', 'blo.org_in_codigo')
                    ->on('un1.org_tau_st_codigo', '=', 'blo.org_tau_st_codigo')
                    ->on('un1.org_pad_in_codigo', '=', 'blo.org_pad_in_codigo')
                    ->on('un1.org_tab_in_codigo', '=', 'blo.org_tab_in_codigo');
            })
            ->join('bild.dbm_estrutura as uni', function (JoinClause $join) {
                $join->on('un1.est_in_codigo', '=', 'uni.est_in_codigo')
                    ->on('un1.org_in_codigo', '=', 'uni.org_in_codigo')
                    ->on('un1.org_tau_st_codigo', '=', 'uni.org_tau_st_codigo')
                    ->on('un1.org_pad_in_codigo', '=', 'uni.org_pad_in_codigo')
                    ->on('un1.org_tab_in_codigo', '=', 'uni.org_tab_in_codigo');
            })
            ->join('bild.car_contrato_cliente as ccl', function (JoinClause $join) {
                $join->on('cto.cto_in_codigo', '=', 'ccl.cto_in_codigo')
                    ->on('cto.org_tab_in_codigo', '=', 'ccl.org_tab_in_codigo')
                    ->on('cto.org_pad_in_codigo', '=', 'ccl.org_pad_in_codigo')
                    ->on('cto.org_in_codigo', '=', 'ccl.org_in_codigo')
                    ->on('cto.org_tau_st_codigo', '=', 'ccl.org_tau_st_codigo');
            })
            ->join('bild.glo_agentes as cli', function (JoinClause $join) {
                $join->on('ccl.agn_tab_in_codigo', '=', 'cli.agn_tab_in_codigo')
                    ->on('ccl.agn_pad_in_codigo', '=', 'cli.agn_pad_in_codigo')
                    ->on('ccl.agn_in_codigo', '=', 'cli.agn_in_codigo');
            })
            ->leftJoin('bild.glo_pessoa_fisica as fis', function (JoinClause $join) {
                $join->on('cli.agn_tab_in_codigo', '=', 'fis.agn_tab_in_codigo')
                    ->on('cli.agn_pad_in_codigo', '=', 'fis.agn_pad_in_codigo')
                    ->on('cli.agn_in_codigo', '=', 'fis.agn_in_codigo')
                    ->where('fis.agn_ch_tipo', '=', 'P');
            })
            ->join('bild.dbm_ocorrenciacontrato as oct', function (JoinClause $join) {
                $join->on('cto.cto_in_codigo', '=', 'oct.cto_in_codigo')
                    ->on('cto.org_tab_in_codigo', '=', 'oct.org_tab_in_codigo')
                    ->on('cto.org_pad_in_codigo', '=', 'oct.org_pad_in_codigo')
                    ->on('cto.org_in_codigo', '=', 'oct.org_in_codigo')
                    ->on('cto.org_tau_st_codigo', '=', 'oct.org_tau_st_codigo');
            })
            ->join('bild.dbm_geraocorrencia as oco', 'oct.oco_in_codigo', '=', 'oco.oco_in_codigo')
            ->where('oco.ocs_in_codigo', 44)
            ->where('oco.ocs_in_modulo', 1)
            ->whereNotNull('env.est_in_codigo')
            ->where('cto.cto_ch_tipo', 'V')
            ->where('cto.cto_ch_status', 'A')
            ->whereRaw("bild.alx_pck_utilafh.F_StatusAgente(cto.org_tab_in_codigo, cto.org_pad_in_codigo, cto.fil_in_codigo, 'G') = 'A'")
            ->where(function (Builder $query) use ($idUnidade, $cpfCliente, $codContrato) {
                $query->where(function (Builder $subQuery) use ($idUnidade, $cpfCliente) {
                    $subQuery->where('env.est_in_codigo', $idUnidade)
                        ->whereRaw("CASE cli.agn_ch_tipopessoafj WHEN 'J' THEN cli.agn_st_cgc ELSE fis.agn_st_cpf END = ?", [$cpfCliente]);
                })
                    ->orWhere('cto.cto_in_codigo', $codContrato);
            })
            ->limit(3)
            ->orderByDesc('oco.oco_dt_cadastro')
            ->get();
    }

    public static function addVinculoMega(
        int $codFilialMega,
        string $codContrato,
        string $cpfCompradorPrincipal,
        string $cpfVinculado,
        int $grau,
        string $percentual,
        string $tipo
    ): void {
        $pdo = self::connection()->getPdo();
        $stmt = $pdo->prepare('BEGIN
                bild.PRC_BLD_ALL_RENDA_AVAL(
                    :p_org_in,
                    :p_cto_in,
                    :p_agn_pr,
                    :p_agn_rd,
                    :p_grau,
                    :p_perc,
                    :p_tipo
                );
            END;');

        $stmt->bindParam(':p_org_in', $codFilialMega, PDO::PARAM_INT);
        $stmt->bindParam(':p_cto_in', $codContrato, PDO::PARAM_INT);
        $stmt->bindParam(':p_agn_pr', $cpfCompradorPrincipal, PDO::PARAM_STR);
        $stmt->bindParam(':p_agn_rd', $cpfVinculado, PDO::PARAM_STR);
        $stmt->bindParam(':p_grau', $grau, PDO::PARAM_INT);
        $stmt->bindParam(':p_perc', $percentual, PDO::PARAM_STR);
        $stmt->bindParam(':p_tipo', $tipo, PDO::PARAM_STR);

        $stmt->execute();
    }
}
