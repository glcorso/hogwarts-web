<?php

namespace Lidere;

use \PDO;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Lidere\Models\Aplicacao;

class Monitor implements MessageComponentInterface
{
    protected $clients;
    protected $v_erro = null;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->conn = array();
    }

    /**
     * Abre conexão com o cliente
     * @param  ConnectionInterface $conn
     * @return void
     */
    public function onOpen(ConnectionInterface $conn)
    {
        echo "==============Nova Conexão:onOpen=================\n";
        // Store the new connection to send messages to later

            $this->clients->attach($conn);

        echo "=============Conexão com o banco:onOpen===========\n";
        $this->conn[$conn->resourceId] = Core::oraclePdoOci(1);
        echo "=============Conexão com o banco:onOpen===========\n";

        echo "Nova Conexão! ({$conn->resourceId})\n";

        echo "==============Nova Conexão:onOpen=================\n";
    }

    /**
     * Faz o carregamento da arvore
     * @param  PDO $conn       conexão
     * @param  integer $produto_id
     * @param  integer $cliente_id
     * @return string             retorna mensagem de erro
     */
    public function carregaArvore($conn = null, $produto_id = null, $cliente_id = null)
    {
        echo "==============Carrega Arvore========================\n";

        $stmt = $conn->prepare(
            "BEGIN
                DB_LIDERE_CONFIG_SULFER.LIMPA_WG_CONFIGURADOR;

                SDI_CARREGA_ARVORE_SULFER(pi_itempr_id  => :produto_id
                                        , pi_cod_preven => NULL
                                        , pi_cli_id     => :cliente_id
                                        , pi_divd_id    => NULL
                                        , po_erro       => :v_erro);
            END;"
        );

        $stmt->bindParam(':produto_id', $produto_id, PDO::PARAM_INT, 10);
        $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT, 10);
        $stmt->bindParam(':v_erro', $v_erro, PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        echo "==============Carrega Arvore========================\n";

        return $v_erro;
    }

    public function buscaCaracteristicas(
        $conn = null,
        $produto_id = null,
        $empr_id = null,
        $cliente_id = null,
        $carac_id = null,
        $carac_tipo = null,
        $resp_id = null,
        $tcarac_id = null,
        $tipo_campo = null,
        $v_ant = null,
        $remove = null
    ) {
        echo "==============Busca Caracteristicas========================\n";

        $this->v_erro = $this->respostaSelecionada(
            $conn,
            $produto_id,
            $empr_id,
            $carac_tipo,
            $carac_id,
            $resp_id,
            $tcarac_id,
            $tipo_campo,
            $v_ant,
            $remove
        );

        $stmt = $conn->prepare(
            "SELECT id            ID
                  , levele        \"LEVEL\"
                  , rownum        LINHA
                  , tipo          TIPO
                  , descricao     DESCRICAO
                  , expand        EXPAND
                  , valor         VALOR
                  , titem_car_id  TITEM_CAR_ID
                  , cor           COR
                  , valor_n_nulo  VALOR_N_NULO
                  , tcarac_id     TCARAC_ID
                  , LEAD(ID,1) OVER (ORDER BY ROWNUM) NEXT_ID
                  , tipo_campo    tipo_campo
                  , ind_preco     ind_preco
            FROM (SELECT wg.id               id
                       , level               levele
                       , DECODE(wg.tipo,'IN','NR','IC','TX',wg.tipo) tipo
                       , RPAD(' ',(LEVEL-1)*5,' ') || DECODE(wg.tipo,'R','',wg.seq ||'- ') || CASE WHEN TRIM(wg.descricao) IS NULL THEN
                                                                                                      '\"NULO\"'
                                                                                                   ELSE
                                                                                                      descricao
                                                                                                   END descricao
                       , CASE WHEN CONNECT_BY_ISLEAF = 1 THEN
                                 ' '
                              ELSE
                                 wg.expand
                              END             expand
                       , wg.preco             valor
                       , wg.arvore_id         titem_car_id
                       , wg.cor               cor
                       , NVL(wg.preco,0)      valor_n_nulo
                       , wg.tcarac_id         tcarac_id
                       , wg.tipo_campo        tipo_campo
                       , wg.ind_preco         ind_preco
                    FROM wg_sdi_config_arv_sulfer wg
                   WHERE visivel = 1
                   START WITH arvore_id IS NULL
                 CONNECT BY PRIOR id = arvore_id
                   ORDER SIBLINGS BY seq)"
        );


        $stmt->execute();

        $data = array();
        while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) != false) {
            $data[] = $row;
        }
        //var_dump($data);
        $caracteristicas = array();
        if (!empty($data)) {
            // Verifica se as caracteristicas já possuem um respostas pré-definida
            $next_id = null;
            foreach ($data as $key => &$caracteristica) {
                // Se for tipo R é uma resposta já selecionada
                if ($caracteristica['TIPO'] !== 'R') {
                    $v_erro = null;
                    //$carac_id = empty($next_id) ? $caracteristica['ID'] : $next_id;
                    //echo 'carac_id:'.$carac_id, 'next_id:'.$next_id;
                    $carac_id = $caracteristica['ID'];
                    $caracteristicas[$key] = $caracteristica;
                    $keysearch = null;
                    foreach ($data as $k => $c) {
                        if ($c['TIPO'] == 'R') {
                            if ($c['TITEM_CAR_ID'] == $carac_id) {
                                $keysearch[] = $k;
                            }
                        }
                    }

                    if (!empty($keysearch)) {
                        if ($caracteristica['TIPO'] != 'ME') {
                            $caracteristicas[$key]['RESPOSTA'] = $data[current($keysearch)];
                        } else {
                            foreach ($keysearch as $search) {
                                $caracteristicas[$key]['RESPOSTA'][] = $data[$search];
                            }
                        }
                    }

                    $resp = array();
                    $selecionaNodo = $this->selecionaNodo($conn, $carac_id, $produto_id);
                    if (empty($selecionaNodo)) {
                        if ($caracteristica['TIPO'] == 'FO' && !is_null($carac_id)) {
                            $caracteristicas[$key]['RESPOSTA'] = $this->carregaFormula($conn, $carac_id);
                        } else {
                            $resp = $this->respostasPossiveis($conn);
                        }
                    } else {
                        $resp = $selecionaNodo;
                    }
                    $caracteristicas[$key]['RESPOSTAS'] = $resp;
                }
                $next_id = !empty($caracteristica['NEXT_ID']) ? $caracteristica['NEXT_ID'] : null;
            }
        }

        echo "==============Busca Caracteristicas========================\n";

        return $caracteristicas;
    }

    public function respostaSelecionada(
        $conn = null,
        $produto_id = null,
        $empr_id = null,
        $carac_tipo = null,
        $carac_id = null,
        $resp_id = null,
        $tcarac_id = null,
        $tipo_campo = null,
        $v_ant = null,
        $remove = null)
    {
        echo "==============Respostas========================\n";
        try {
            $v_erro = null;
            $v_ant = empty($v_ant) ? $resp_id : $v_ant;
            var_dump(array($conn, $produto_id, $carac_tipo, $carac_id, $resp_id, $tcarac_id, $tipo_campo, $v_ant, $remove));
            if (!empty($carac_id)) {
                $sql = null;
                if ($carac_tipo == 'ME') {
                    if ($remove) {
                        $sql =
                        "BEGIN
                             SDI_RETIRA_ESCOLHA(p_itempr_id => :produto_id
                                              , p_titens_car_id =>  :carac_id
                                              , p_tcarac_id => :tcarac_id
                                              , p_tipo => :carac_tipo
                                              , p_tvar_id => :v_ant
                                              , po_erro => :v_erro);

                            SDI_SELECIONA_NODO(pi_arvore_id => :carac_id
                                             , pi_itempr_id => :produto_id
                                             , po_erro      => :v_erro);
                        END;";
                    } else {
                        $sql =
                        "BEGIN
                            SDI_SELECIONA_NODO(pi_arvore_id => :carac_id
                                             , pi_itempr_id => :produto_id
                                             , po_erro      => :v_erro);

                            SDI_RESPONDE_ESCOLHA(p_tipo => :carac_tipo
                                               , p_titens_car_id => :carac_id
                                               , p_tvar_id => :resp_id
                                               , p_tcarac_id => :tcarac_id
                                               , po_erro => :v_erro);

                            SDI_SELECIONA_NODO(pi_arvore_id => :carac_id
                                             , pi_itempr_id => :produto_id
                                             , po_erro      => :v_erro);
                        END;";
                    }
                } elseif ($carac_tipo == 'ES') {
                    $sql =
                "BEGIN
                    SDI_SELECIONA_NODO(pi_arvore_id => :carac_id
                                     , pi_itempr_id => :produto_id
                                     , po_erro      => :v_erro);

                    SDI_RETIRA_ESCOLHA(p_itempr_id => :produto_id
                                     , p_titens_car_id =>  :carac_id
                                     , p_tcarac_id => :tcarac_id
                                     , p_tipo => :carac_tipo
                                     , p_tvar_id => :v_ant
                                     , po_erro => :v_erro);

                    SDI_RESPONDE_ESCOLHA(p_tipo => :carac_tipo
                                       , p_titens_car_id => :carac_id
                                       , p_tvar_id => :resp_id
                                       , p_tcarac_id => :tcarac_id
                                       , po_erro => :v_erro);
                END;";
                } elseif ($carac_tipo == 'OP') {
                    $sql =
                "BEGIN
                    SDI_SELECIONA_NODO(pi_arvore_id => :carac_id
                                     , pi_itempr_id => :produto_id
                                     , po_erro      => :v_erro);

                    SDI_RESPONDE_OPCIONAL(p_itempr_id => :produto_id
                                        , p_tcarac_id => :tcarac_id
                                        , p_titens_car_id => :carac_id
                                        , p_opcao => :opcao
                                        , po_erro => :v_erro);
                END;";
                } elseif ($carac_tipo == 'CA' || $carac_tipo == 'DE' || $carac_tipo == 'NR' || $carac_tipo == 'TX') {
                    $sql =
                "BEGIN
                    SDI_RESP_CA_TX_NR_DE ( p_itempr_id     => :produto_id
                                         , p_tcarac_id     => :tcarac_id
                                         , p_titens_car_id => :carac_id
                                         , p_resposta      => :resposta
                                         , p_tipo_campo    => :tipo_campo
                                         , p_tipo          => :carac_tipo
                                         , p_empr_id       => :empr_id
                                         , po_erro         => :v_erro);
                END;";
                }

                if (!empty($sql)) {
                    $stmt = $conn->prepare($sql);
                    /*$v_erro_nodo1 = null;
                    $v_erro_escolha = null;
                    $v_erro_nodo2 = null;
                    $v_erro_nodo = null;
                    $v_erro_retira = null;
                    $v_erro_escolha = null;
                    $v_erro_opcional = null;*/
                    if ($carac_tipo == 'ME') {
                        echo "===================ME================\n";
                        $stmt->bindParam(":produto_id", $produto_id, PDO::PARAM_INT, 10);
                        $stmt->bindParam(":carac_tipo", $carac_tipo, PDO::PARAM_INT, 10);
                        $stmt->bindParam(":carac_id", $carac_id, PDO::PARAM_INT, 10);
                        if ($remove) {
                            $stmt->bindParam(":v_ant", $v_ant, PDO::PARAM_INT, 10);
                        } else {
                            $stmt->bindParam(":resp_id", $resp_id, PDO::PARAM_INT, 10);
                        }
                        $stmt->bindParam(":tcarac_id", $tcarac_id, PDO::PARAM_INT, 10);

                        //$stmt->bindParam(":v_erro_nodo", $v_erro_nodo, PDO::PARAM_INPUT_OUTPUT, 4000);
                        //$stmt->bindParam(":v_erro_escolha", $v_erro_escolha, PDO::PARAM_INPUT_OUTPUT, 4000);
                    } elseif ($carac_tipo == 'ES') {
                        echo "===================ES===============\n";
                        $stmt->bindParam(':produto_id', $produto_id, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':carac_tipo', $carac_tipo, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':carac_id', $carac_id, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':resp_id', $resp_id, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':v_ant', $v_ant, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':tcarac_id', $tcarac_id, PDO::PARAM_INT, 10);

                        //$stmt->bindParam(':v_erro', $v_erro, PDO::PARAM_INPUT_OUTPUT, 4000);
                    } elseif ($carac_tipo == 'OP') {
                        echo "==================OP================\n";
                        $stmt->bindParam(':produto_id', $produto_id, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':carac_tipo', $carac_tipo, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':carac_id', $carac_id, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':opcao', $resp_id, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':tcarac_id', $tcarac_id, PDO::PARAM_INT, 10);

                        //$stmt->bindParam(':v_erro_nodo', $v_erro_nodo, PDO::PARAM_INPUT_OUTPUT, 4000);
                        //$stmt->bindParam(':v_erro_opcional', $v_erro_opcional, PDO::PARAM_INPUT_OUTPUT, 4000);
                    } elseif ($carac_tipo == 'CA' || $carac_tipo == 'DE' || $carac_tipo == 'NR' || $carac_tipo == 'TX') {
                        echo "==================".$carac_tipo."================\n";
                        $stmt->bindParam(':produto_id', $produto_id, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':tcarac_id', $tcarac_id, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':carac_id', $carac_id, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':resposta', $resp_id, $carac_tipo == 'NR' ? PDO::PARAM_INT : PDO::PARAM_STR);
                        $stmt->bindParam(':tipo_campo', $tipo_campo, PDO::PARAM_STR);
                        $stmt->bindParam(':carac_tipo', $carac_tipo, PDO::PARAM_INT, 10);
                        $stmt->bindParam(':empr_id', $empr_id, PDO::PARAM_INT, 10);
                    }

                    $stmt->bindParam(':v_erro', $v_erro, PDO::PARAM_INPUT_OUTPUT, 4000);

                    $stmt->execute();

                    var_dump($sql);
                    var_dump(array($produto_id, $carac_tipo, $carac_id, $resp_id, $tcarac_id, $v_ant));
                    var_dump($v_erro);

                    /*$v_erro = array(
                        'v_erro_nodo1' => $v_erro_nodo1,
                        'v_erro_escolha' => $v_erro_escolha,
                        'v_erro_nodo2' => $v_erro_nodo2,
                        'v_erro_nodo' => $v_erro_nodo,
                        'v_erro_retira' => $v_erro_retira,
                        'v_erro_escolha' => $v_erro_escolha,
                        'v_erro_opcional' => $v_erro_opcional
                    );*/
                }
            }
        } catch (\Exception $e) {
            var_dump($e);
        }
        echo "==============Respostas========================\n";

        return !empty($v_erro) ? $v_erro : null; //implode(' - ', $v_erro) : null;
    }

    /**
     * Seleciona Nodo
     * @param  integer $carac_id    Id da categoria
     * @param  integer $produto_id  Id do produto
     * @return string               Retorna null ou o erro caso houver
     */
    public function selecionaNodo($conn = null, $carac_id = null, $produto_id = null)
    {
        echo "==============Seleciona Nodo========================\n";
        $v_erro = null;
        if (!empty($carac_id) && !empty($produto_id)) {
            $sql = "BEGIN
                        SDI_SELECIONA_NODO(pi_arvore_id => :carac_id
                                         , pi_itempr_id => :produto_id
                                         , po_erro      => :v_erro);
                    END;";
            //var_dump($sql);
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':carac_id', $carac_id, PDO::PARAM_INT, 10);
            $stmt->bindParam(':produto_id', $produto_id, PDO::PARAM_INT, 10);
            $stmt->bindParam(':v_erro', $v_erro, PDO::PARAM_INPUT_OUTPUT, 4000);

            $stmt->execute();
            //var_dump(array($carac_id, $produto_id, $v_erro));
        } else {
            $v_erro = 'Id da caracteristica/produto não infomado!';
        }
        echo "==============Seleciona Nodo========================\n";

        return !empty($v_erro) ? $v_erro : null;
    }

    /**
     * Retorna a formula da caracteristica FO
     * @param  integer $carac_id Id da caracteristica
     * @return mixed             Retorna null ou a fórmula
     */
    public function carregaFormula($conn = null, $carac_id = null)
    {
        echo "==============Carrega Formula========================\n";
        $v_erro = null;
        $formula = null;
        if (!empty($carac_id)) {
            $sql = "BEGIN
                        SDI_CARREGA_FORMULA(p_titens_car_id => :carac_id
                                          , po_formula      => :formula
                                          , po_erro         => :v_erro);
                    END;";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':carac_id', $carac_id, PDO::PARAM_INT, 10);
            $stmt->bindParam(':formula', $formula, PDO::PARAM_INPUT_OUTPUT, 4000);
            $stmt->bindParam(':v_erro', $v_erro, PDO::PARAM_INPUT_OUTPUT, 4000);

            $stmt->execute();
            //var_dump($sql);
            //var_dump(array($carac_id, $formula, $v_erro));
        } else {
            $v_erro = 'Id da caracteristica não informado';
        }

        echo "==============Carrega Formula========================\n";

        return !empty($v_erro) ? $v_erro : $formula;
    }

    /**
     * Retorna as respostas possíveis de cada caracteristica
     * @return array
     */
    public function respostasPossiveis($conn = null)
    {
        echo "==============Respostas Possíveis========================\n";
        $sql = "SELECT rp.*
                FROM WG_SDI_TCARAC_ITEM_RESP_POS rp
                ORDER BY rp.DESCRICAO";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // TVAR_ID, COD_VAR, DESCRICAO, MNEMONICO, PRECO TRESTT_DEPS_ID, IND_PRECO
        $resp = array();
        while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) != false) {
            $resp[] = $row;
        }

        echo "==============Repostas Possivies========================\n";

        return $resp;
    }

    public function validaConfigurado($conn = null, $produto_id = null)
    {
        echo "==============Valida Configurado========================\n";

        $v_erro = null;
        $v_erro_masc = null;
        if (!empty($produto_id)) {
            $sql = "BEGIN
                        SDI_VALIDA_CONFIGURADO(p_itempr_id => :produto_id
                                             , po_tmasc_item_id => :masc_id
                                             , po_erro_masc => :v_erro_masc
                                             , po_erro => :v_erro);
                    END;";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':produto_id', $produto_id, PDO::PARAM_INT, 10);
            $stmt->bindParam(':masc_id', $masc_id, PDO::PARAM_INPUT_OUTPUT, 10);
            $stmt->bindParam(':v_erro_masc', $v_erro_masc, PDO::PARAM_INPUT_OUTPUT, 4000);
            $stmt->bindParam(':v_erro', $v_erro, PDO::PARAM_INPUT_OUTPUT, 4000);

            $stmt->execute();

            $v_erro = !empty($v_erro) ? $v_erro : $v_erro_masc;
        } else {
            $v_erro = 'Id do produto não informado';
        }

        return !empty($v_erro) ? $v_erro : (int)$masc_id;
    }

    public function buscaTabelaVenda($conn = null, $cod_preven = null, $empr_id = null)
    {
        echo "==============Tabela de Venda========================\n";

        $row = array();
        if (!empty($cod_preven) && !empty($empr_id)) {
            $sql = "SELECT * FROM tprecosven WHERE cod_preven = {$cod_preven} AND empr_id = {$empr_id}";

            $stmt = $conn->prepare($sql);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $row['ID'] = 'cod_preven ou empr_id não informado';
        }

        return !empty($row['ID']) ? $row['ID'] : null;
    }

    public function buscaMascaraConfigurado($conn = null, $mascara_id = null)
    {
        echo "==============Mascara Configurado========================\n";

        $row = array();
        if (!empty($mascara_id)) {
            $sql = "SELECT focco3i_itens.RETORNA_DESCRICAO_CONFIGURADO({$mascara_id}) mascara from dual";

            $stmt = $conn->prepare($sql);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $row['MASCARA'] = 'Id da mascara não informada!';
        }

        return !empty($row['MASCARA']) ? $row['MASCARA'] : null;
    }

    public function buscaValorConfigurado($conn = null, $orcamento = null, $empr_id = null, $masc_item_id = null, $prven_id = null, $cliente_id = null)
    {
        echo "==============Valor Configurado========================\n";

        $v_erro = null;
        $v_preco = 0;
        if (!empty($orcamento) && !empty($empr_id) && !empty($masc_item_id)) {
            $sql = "BEGIN
                       :v_preco := SDI_RETORNA_VALOR_CONFIGURADO(p_empr_id      => :empr_id
                                                              , p_tmasc_item_id => :masc_item_id
                                                              , p_tprven_id     => :prven_id
                                                              , p_cli_id        => :cliente_id
                                                              , p_num_orc       => :num_orc
                                                              , po_erro         => :v_erro);
                    END;";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':empr_id', $empr_id, PDO::PARAM_INT, 10);
            $stmt->bindParam(':masc_item_id', $masc_item_id, PDO::PARAM_INT, 10);
            $stmt->bindParam(':prven_id', $prven_id, PDO::PARAM_INT, 10);
            $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT, 10);
            $stmt->bindParam(':num_orc', $orcamento, PDO::PARAM_STR);
            $stmt->bindParam(':v_erro', $v_erro, PDO::PARAM_INPUT_OUTPUT, 4000);
            $stmt->bindParam(':v_preco', $v_preco, PDO::PARAM_INPUT_OUTPUT, 4000);

            $stmt->execute();
        } else {
            $v_erro = 'orcamento, empr_id, masc_item_id, prven_id ou cliente_id não informados: ['.print_r(array($orcamento, $empr_id, $masc_item_id, $prven_id, $cliente_id), true).']';
        }

        var_dump($v_erro, $v_preco);

        return $v_preco == 0 ? $v_erro : $v_preco;
    }

    public function apagaMascara($conn = null)
    {
        echo "==============Valor Configurado========================\n";

        $v_erro = null;
        $sql = "BEGIN
                    SDI_APAGA_MASCARA(v_erro => :v_erro);
                END;";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':v_preco', $v_preco, PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return !empty($v_erro) ?  $v_erro : null;
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        var_dump(dirname(__FILE__));
        //$arqLog = fopen(dirname(__FILE__).'/../logs/socket.log', 'a+');

        $numRecv = count($this->clients) - 1;
        echo sprintf('Nova conexão %d possuindo a sessão do sistema: "%s" to %d other connection%s' . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $array = null;
        $return = new \stdClass();
        $return->error = false;
        $return->msg = '';
        $return->respostas = array();
        $return->caracteristicas = array();
        foreach ($this->clients as $client) {
            // Envia somente para o remetente
            if ($from == $client) {
                // var_dump($from);
                // var_dump($client);
                $return->resourceId = $from->resourceId;
                echo "==============Cliente:{$from->resourceId}:onMessage========================\n";
                /**
                 * Recupera a conexão ao banco
                 * @var PDO
                 */
                echo "==============Recupera Conexão Banco:onMessage========================\n";
        $conn = $this->conn[$from->resourceId];
        var_dump($conn);
                echo "==============Recupera Conexão Banco:onMessage========================\n";
                // var_dump(intval($from->resourceId));
                /*
                class stdClass#72 (6) {
                    public $orcamento =>
                    string(5) "58850"
                    public $produtoId =>
                    string(5) "83148"
                    public $clienteId =>
                    NULL
                    public $token =>
                    string(20) "Aw9nCpSkndgJIXtjpXsV"
                    public $arvore =>
                    bool(true)
                    public $caracteristicas =>
                    bool(true)
                }
                */
                $obj = json_decode($msg);
                $return->vant = !empty($obj->vant) ? $obj->vant : null;
                $return->next = !empty($obj->next) ? $obj->next : null;
                var_dump($obj);

                // ws.send(JSON.stringify({ orcamento: '58850', produtoId: '83148', clienteId: null, token: 'Aw9nCpSkndgJIXtjpXsV', arvore: true, caracteristicas: true }));
                if (!empty($obj->arvore)) {
                    if (!empty($obj->produtoId)) {
                        $retorno = $this->carregaArvore(
                            $conn,
                            $obj->produtoId,
                            $obj->clienteId
                        );

                        if (!empty($retorno)) {
                            $return->error = true;
                            $return->msg = $retorno;
                        }
                    } else {
                        $return->error = true;
                        $return->msg = 'Id do produto não informado!';
                    }
                }

              // ws.send(JSON.stringify({ orcamento: '58850', produtoId: '83148', clienteId: null, token: 'Aw9nCpSkndgJIXtjpXsV', resposta: true, caracteristicas: true, respostas: '38456#ES#8458#3208' }));
              if (!empty($obj->caracteristicas)) {
                  $carac_id = null;
                  $carac_tipo = null;
                  $resp_id = null;
                  $tcarac_id = null;
                  $tipo_campo = null;
                  $v_ant = null;
                  $remove = !empty($obj->remove) ? $obj->remove : null;
                  if (!empty($obj->respostas)) {
                      if (strpos($obj->resposta, '#') !== false) {
                          list($carac_id, $carac_tipo, $resp_id, $tcarac_id, $tipo_campo) = explode('#', $obj->resposta);
                      }
                      $v_ant = !empty($obj->vant) ? $obj->vant : $resp_id;
                      foreach ($obj->respostas as $resposta) {
                          $tmpcarac_id = null;
                          $tmpcarac_tipo = null;
                          $tmpresp_id = null;
                          $tmpcarac_id = null;
                          $tmptipo_campo = null;
                          if (strpos($resposta->resp_id, '#') !== false) {
                              list($tmpcarac_id, $tmpcarac_tipo, $tmpresp_id, $tmptcarac_id, $tmptipo_campo) = explode('#', $resposta->resp_id);
                          }
                          $return->respostas[$resposta->carac_id] = $tmpresp_id != 'undefined' ? $tmpresp_id : '';
                      }
                  }

                   /**
                    * Busca as caracteristicas do produto
                    * @var array
                    */
                    $return->caracteristicas = $this->buscaCaracteristicas(
                        $conn,
                        $obj->produtoId,
                        $obj->empresaId,
                        $obj->clienteId,
                        $carac_id,
                        $carac_tipo,
                        $resp_id,
                        $tcarac_id,
                        $tipo_campo,
                        $v_ant,
                        $remove
                    );

                  if (!empty($this->v_erro)) {
                      $return->error = true;
                      $return->msg = $this->v_erro;
                  }
              }

                if (!empty($obj->validaConfigurado)) {
                    $validaConfigurado = $this->validaConfigurado($conn, $obj->produtoId);
                    $mascaraId = null;
                    if (is_string($validaConfigurado)) {
                        $v_erro = $this->apagaMascara($conn);
                        $return->error = true;
                        $return->msg = $v_erro ? $v_erro : $validaConfigurado;
                    } else {
                        $mascaraId = $validaConfigurado;
                        $tabela = $this->buscaTabelaVenda($conn, $obj->codPreven, $obj->empresaId);
                        if (is_numeric($tabela)) {
                            $return->mascara = $this->buscaMascaraConfigurado($conn, $mascaraId);

                            /**
                             * Busca o valor do produto configurado
                             * @var string
                             */
                            $valor = $this->buscaValorConfigurado(
                                $conn,
                                $obj->orcamento,
                                $obj->empresaId,
                                $mascaraId, // Mascara do configurado 2566873
                                $tabela, // Tabela de venda 8562
                                $obj->clienteId // Id do cliente 12846
                            );
                            var_dump('Valor:'.$valor);

                            if (is_numeric($valor)) {
                                $return->valor = number_format((float)$valor, 2, '.', '');
                            } else {
                                $v_erro = $this->apagaMascara($conn);
                                $return->error = true;
                                $return->msg = $v_erro ? $v_erro : $valor;
                            }
                        }
                    }
                }

              //$array['socket_id'] = intval($from->resourceId);
              //$array['system_id'] = intval($msg);
              //fwrite($arqLog, print_r($array, true));
              //$this->aplicacaoObj->insertSessao('tsessoes_ativas', $array);
                echo "==============Envia mensagem:onMessage========================\n";
                $client->send(json_encode($return));
                echo "==============Envia mensagem:onMessage========================\n";

                echo "==============Cliente:{$from->resourceId}:onMessage========================\n";
            }
        }
        //fclose($arqLog);
    }

    public function onClose(ConnectionInterface $conn)
    {
        /**
         * Recupera a conexão ao banco
         * @var PDO
         */
        echo "==============Recupera Conexão Banco:onClose========================\n";
        $db = !empty($this->conn[$conn->resourceId]) ? $this->conn[$conn->resourceId] : false;
        echo "==============Recupera Conexão Banco:onClose========================\n";
    if ($db) {
            // apaga mascara para não ficar lixo
        $this->apagaMascara($db);
    }

        // remove conexão
        unset($this->conn[$conn->resourceId]);

        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        //$this->aplicacaoObj->deleteByColumn('tsessoes_ativas',array('socket_id' => $conn->resourceId));
        echo "A Conexão {$conn->resourceId} foi desconectada\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Um erro ocorreu: {$e->getMessage()}\n";

        unset($this->conn[$conn->resourceId]);

        $conn->close();
    }
}
