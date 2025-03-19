<?php

namespace Nave\IssSatellite;

use Illuminate\Support\Facades\DB;

class Mega
{
    static protected function connectionConfig()
    {
        config([
            'database.connections.iss-satellite-mega' => config('iss-satellite.mega.db'),
        ]);
    }

    static public function connection(): \Illuminate\Database\Connection
    {
        self::connectionConfig();
        return DB::connection('iss-satellite-mega');
    }

    /**
     * NÃO TESTADO
     * Função que atualiza os dados do cliente
     * 
     * @param array $data
     * @return int
     */
    static public function atualizaDadosCliente($data): int
    {
        $query = " update bild.glo_agentes t
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
                    and t.agn_tab_in_codigo = :agn_tab_in_codigo";
        return self::connection()->update($query, $data);
    }

    /**
     * 
     * @return array
     */
    static public function getEstadosCivis(): array
    {
        $query = 'select * from bild.glo_estadocivil';
        return self::connection()->select($query);
    }

    /**
     * Função que pega os dados do cliente pelo cpf/cnpj
     * 
     * @return array
     */
    static public function clientes($data): array
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
     * 
     * Função que pega os dados do cliente pelo cto_in_codigo
     * 
     * @return array
     */
    static public function clienteByCtoInCodigo($data): array
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
     * 
     * @return array
     */
    static public function clientesSac($data): array
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
            from  bild.vw_bld_bca_cto_cli_api ";
        if (!empty($data['document']) && empty($data['agn_st_nome'])) {
            $query .= "WHERE AGN_ST_CPF = ':document'
                        OR CNPJ = ':document'";
        } elseif (empty($data['document']) && !empty($data['agn_st_nome'])) {
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
     * 
     * @return array
     */
    static public function getEntregaDeChaveByCtoInCodigo($data): array
    {
        $query = "select * from bild.vw_bld_ono_contr_ocor_sys_api
            where cod_contrato = :cto_in_codigo";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO IMPLEMENTADO - Não é Mega
     * API SYS MegaController::getVizinhosByCodUnidade
     */

    /**
     * NÃO IMPLEMENTADO - Alias de buscaDadosUnidade
     * API SYS MegaController::contratoByCtoInCodigo
     */

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function buscaDadosUnidade($data): array
    {
        $query = "select *
                    from bild.vw_bld_ono_contrato_sys_api
                where cto_in_codigo = :cto_in_codigo";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function contratos($data): array
    {
        $query = "select *
                from bild.vw_bld_ono_contrato_sys_api
            where agn_in_codigo = :agn_in_codigo";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * Traz os status do boleto de sinal
     * 
     * @return array
     */
    static public function statusBoletoSinal($data): array
    {
        $query = "select t.*
                from bild.alx_viw_bldbolrapido t
                where t.agn_in_codigo = :agn_in_codigo";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * Traz o extrato do cliente, mesma table de extratoFinanceiro, porém essa não traz duplicado
     * 
     * @return array
     */
    static public function sacExtratoCliente($data): array
    {
        $query = "select *
                from bild.vw_bld_ono_flu_cli_rep_sys_api
                where codigo_contrato_mega = :cto_in_codigo";
        return self::connection()->select($query, $data);
    }

    /**
     * Lista todos os empreendimentos
     * 
     * @return array
     */
    static public function empreendimentos(): array
    {
        $query = "select *
                from bild.vw_bld_ono_est_emp_sys_api";
        return self::connection()->select($query);
    }

    /**
     * NÃO TESTADO
     * Mostra todo o fluxo financeiro do contrato
     * 
     * @return array
     */
    static public function extratoFinanceiro($data): array
    {
        $data['date'] = date('Y-m-d');
        $$data['seq'] = 1;

        $query = "begin
            bild.alx_pck_bldapp.fnc_bld_app_parcela (:organizacao
            , :cto_in_codigo
            , :sequencia
            , :data_base);
            end;";

        self::connection()->select($query, $data);

        $query = "select t.* from bild.alx_clitmpcontratos t
                where t.org_in_codigo = :organizacao and
                t.cto_in_codigo = :cto_in_codigo and t.seq_in_codigo = :sequencia";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * Mostra todo o fluxo financeiro do contrato
     * 
     * @return array
     */
    static public function extratoFinanceiroDataBase($data): array
    {
        $query = "select vbo.org_in_codigo
                    , vbo.cto_in_codigo
                    , vbo.cto_ch_status
                    , vbo.codigo_exporta
                    , vbo.documento
                    , vbo.cto_dt_cadastro
                    , vbo.cto_re_valorcontrato
                    , vbo.cto_re_valorcontrato_ori
                    , vbo.par_ch_parcela
                    , vbo.par_ch_receita
                    , vbo.par_in_codigo
                    , vbo.par_re_valororiginal
                    , vbo.par_dt_vencimento
                    , vbo.par_dt_baixa
                    , vbo.sequencia
                    , vbo.par_ch_status
                    , vbo.par_dt_movimento
                from bild.vw_bld_ono_parcela_sys_api                             vbo
                    , (select distinct
                            cpa.org_tab_in_codigo_new  as org_tab_in_codigo
                            , cpa.org_pad_in_codigo_new  as org_pad_in_codigo
                            , cpa.org_in_codigo_new      as org_in_codigo
                            , cpa.org_tau_st_codigo_new  as org_tau_st_codigo
                            , cpa.cto_in_codigo_new      as cto_in_codigo
                            , cpa.par_in_codigo_new      as par_in_codigo
                            , 'S'                        as parcela_alterada
                        from bild.a#car_parcela   cpa
                            , bild.adt_ocorrencia  aoc
                        where cpa.ado_in_ocorrencia = aoc.ado_in_ocorrencia
                        and aoc.ado_ch_operacao       in ('I','U')
                        and trunc(aoc.ado_dt_inclusao) = to_date(':data_base', 'dd/mm/yyyy') - 1)  qry_par
                where vbo.org_tab_in_codigo = qry_par.org_tab_in_codigo (+)
                and vbo.org_pad_in_codigo = qry_par.org_pad_in_codigo (+)
                and vbo.org_in_codigo     = qry_par.org_in_codigo     (+)
                and vbo.org_tau_st_codigo = qry_par.org_tau_st_codigo (+)
                and vbo.cto_in_codigo     = qry_par.cto_in_codigo     (+)
                and vbo.par_in_codigo     = qry_par.par_in_codigo     (+)";
        
        if ($data['est_in_codigo'] != '0') {
            $query .= "and vbo.codigo_exporta = :est_in_codigo ";
        }
        if ($data['cpf_cnpj'] != '0') {
            $query .=  "and documento = ':cpf_cnpj' ";
        }
        if (($data['cpf_cnpj'] == '0') && ($data['est_in_codigo'] == '0')) {
            $query .= " and (nvl(qry_par.parcela_alterada, 'N') = 'S' or trunc(vbo.cto_dt_status) = :data_base - 1)";
        }

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * Retorno a lista de boletos com linha digitavel (liberado pelo financeiro)
     * 
     * @return array
     */
    static public function boletos($data): array
    {
        $query = "begin
            bild.alx_pck_bldapp.fnc_bld_app_parcela (:organizacao
            , :cto_in_codigo
            , :sequencia
            , :data_base);
            end;";
        self::connection()->select($query, $data);

        $query = "select t.* from bild.alx_clitmpcontratos t
                where t.org_in_codigo = :org_in_codigo
                and t.cto_in_codigo = :cto_in_codigo
                and t.seq_in_codigo = :sequencia
                and t.par_st_linhadigitavel is not null";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function usuarios($data): array
    {
        $pResult = '';

        $query = "begin
            bild.alx_pck_bldallapisharepoint.p_busca_usuario (:pResult
                , :pCod
                , :pNome
                , :pTipo);
            end;";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function insereUsuario($data): array
    {
        $pResult = '';

        $query = " begin
            bild.alx_pck_bldallapisharepoint.p_insere_usuario_mega (:plogin
                , :pnome
                , :pemail
                , :pusubase
                , :pcopia_cc
                , :pcopia_proj
                , :pcopia_org
                , :pcopia_mat
                , :pcopia_grupos);
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function spes($data): array
    {
        $pResult = '';

        $query = "begin
            bild.alx_pck_bldallapisharepoint.p_busca_spe (:pResult
                , :pTexto
                , :pCod
                , :pTipo);
            end;";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function spesUsuarios($data): array
    {
        $pResult = '';

        $query = "begin
            bild.alx_pck_bldallapisharepoint.p_busca_spe_usuario (:pResult
                    , :pOperacao
                    , :pUsuario);
            end;";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function perfilUsuario($pUsuario): array
    {
        $query = " begin
                bild.alx_pck_bldallapisharepoint.p_busca_perfil_usuario (:pResult
                    , :pUsuario);
            end;";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function inclusaoAlcada($data): array
    {
        $query = " begin
                bild.alx_pck_bldallapisharepoint.p_busca_spe_inclusao_alcada (:pResult
                    , :pUsuario);
            end;";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function alcadaUsuario($data): array
    {
        $pResult = '';

        $query = "begin
            bild.alx_pck_bldallapisharepoint.p_busca_alcada_usuario (:pResult
                , :pUsuario);
            end;";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function insereSpeUsuario($data): array
    {
        $query = "begin
                    bild.alx_pck_bldallapisharepoint.p_insere_spe_usuario (:pUsuario
                    , :pOrg_tab
                    , :pOrg_pad
                    , :pFil_Cod
                    , :pOrg_tau
                    , :pPerfil);
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function deleteSpeUsuario($data): array
    {
        $query = "begin
            bild.alx_pck_bldallapisharepoint.p_deleta_spe_usuario (:pUsuario
                , :pFil_Cod);
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function atualizaAlcadaUsuario($data): array
    {
        $query = "begin
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
            end;";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function deletaAlcadaUsuario($data): array
    {
        $query = "begin
            bild.alx_pck_bldallapisharepoint.p_deleta_alcada_usuario (:pUsuario
                , :pOrg_tab
                , :pOrg_pad
                , :pOrg_cod
                , :pOrg_tau
                , :pFil_cod);
            end;";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO IMPLEMENTADO - Utiliza banco SYS
     * API SYS MegaController::ecommerce
     */

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function clientesPorTelefone($data): array
    {
        $query = "select * from bild.vw_bld_ono_cli_contr_sys_api
            where celular =':phone'";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function torresPorEmpreendimento($data): array
    {
        $query = "select * from bild.dbm_vw_estrutura e where e.emp_codigo = :realEstateDevelopmentId ";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function torresPorUnidade($data): array
    {
        $query = "select * from bild.dbm_vw_estrutura e
                where e.und_codigo = :unitId ";
        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function buscaPedidosSuprimentos($data): array
    {
        $query = "begin
            bild.alx_pck_bldallapisuprimentos.p_busca_pedidos (:pResult
                , :pDocumento
                , :pData_ini);
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function buscaContratosSuprimentos($data): array
    {
        $query = "begin
            bild.alx_pck_bldallapisuprimentos.p_busca_contratos (:pResult
                , :pDocumento
                , :pData_ini);
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO IMPLEMENTADO - Utiliza ORM rudimentar do Mega, ver se vai importar
     * API SYS MegaController::getTelefonesByAgnCodigo
     */

    /**
     * NÃO IMPLEMENTADO - Utiliza ORM rudimentar do Mega, ver se vai importar
     * API SYS MegaController::insertTelefones
     */

     /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function fluxoCliente($data): array
    {
        $query = "select *
                from bild.vw_bld_ono_flu_cli_rep_sys_api
            where codigo_unidade_mega = :id_unidade
                and (cpf = ':cpf_cliente' or cnpj = ':cpf_cliente')";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function verificaStatusNF($data): array
    {
        $query = "begin
            bild.alx_pck_bldallapisuprimentos.p_verifica_nota (:pREtorno
                , :pFil_doc
                , :pFor_doc
                , :pNota
                , :pData_Emissao);
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function getDadosTermo($data): array
    {
        $query = "begin
            bild.alx_pck_bldonointegracao.prc_rec_dados_termo (:p_in_cod_exporta
                , :p_in_tipo_doc
                , :p_in_cpf_cnpj
                , :p_in_termo
                , :p_in_usuario
                , :p_in_computador
                , :p_out_result);
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function getDespesaITBI($data): array
    {
        $query = "begin
                bild.alx_pck_bldonointegracao.prc_rec_despesas_itbi (:p_in_cod_exporta
                    , :p_in_tipo_doc
                    , :p_in_cpf_cnpj
                    , :p_in_termo
                    , :p_in_usuario
                    , :p_in_computador
                    , :p_out_result);
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function getFluxoAssociativo($data): array
    {
        $query = "begin
                bild.alx_pck_bldonointegracao.prc_rec_status_fluxo_assoc (:p_in_cod_exporta
                    , :p_in_tipo_doc
                    , :p_in_cpf_cnpj
                    , :p_in_usuario
                    , :p_in_computador
                    , :p_out_result);
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function atualizaFluxoAssociativo($data): array
    {
        $query = "begin
            bild.alx_pck_bldonointegracao.prc_atualiz_status_fluxo_assoc (:p_in_cod_exporta
                , :p_in_tipo_doc
                , :p_in_cpf_cnpj
                , :p_in_status
                , :p_in_usuario
                , :p_in_computador
                , :p_out_result);
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function atualizaDataBancoEscFluxoAssociativo($data): array
    {
        $query = "begin
            bild.alx_pck_bldonointegracao.prc_atualiz_datas_fluxo_assoc (:p_in_cod_exporta
                , :p_in_tipo_doc
                , :p_in_cpf_cnpj
                , :p_in_tipo
                , :p_in_data
                , :p_in_usuario
                , :p_in_computador
                , :p_out_result);
                end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function consultaRM($data): array
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
     * 
     * @return array
     */
    static public function consultaCVV($data): array
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
     * 
     * @return array
     */
    static public function getdataAssembleia($data): array
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
     * 
     * @return array
     */
    static public function addContaBancariaCliente($data): array
    {
        $query = "begin
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
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO TESTADO
     * 
     * @return array
     */
    static public function getContaBancariaCliente($data): array
    {
        $query = "begin
            bild.alx_pck_bldbcaintegracaosys.prc_bld_bca_rec_cc_cli (p_out_result    => :p_out_result
                , p_documento     => :p_documento -- CPF ou CPNJ do Cliente;
                , p_est_in_codigo => :p_est_in_codigo); -- Código exporta da unidade;
            end;";

        return self::connection()->select($query, $data);
    }

    /**
     * NÃO IMPLEMENTADO - Rotina de buscar no Mega por permutantes e inserir no SYS
     * API SYS MegaController::sincronizaPermutantes
     */
}
