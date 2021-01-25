<?php

namespace Lidere\Modules\AssistenciaExterna\Models;

use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use Yajra\Oci8\Eloquent\OracleEloquent;



/**
 * Model para retorno dos dados do banco
 *
 * @category   Controllers
 * @package    Modules
 * @subpackage RelatorioVisitas
 *
 * @author    Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright 2019 Lidere Sistemas
 * @license   GPL-3 https://www.lideresistemas.com.br/licence.txt
 * @link      https://www.lideresistemas.com.br/
 */
class OrdemServicoAutList extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'vsdi_assistencia_ext_ordem_aut';

    public $timestamps = false;
    public $sequence = false;

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }


    public static function retornaValorLinha($ordem_id){
        $sql = "SELECT v4.categoria_id, v4.ordem_id, v4.servico_id, v4.valor_servico,
                       v4.valor_categoria, v4.cod_serv, v4.desc_serv, v4.cod_cat, v4.desc_cat,
                       v4.qtde_serv_cat,
                       CASE
                          WHEN valor_categoria > valor_servicos_somados
                             THEN valor_servicos_somados
                          ELSE valor_categoria
                       END valor_calculado
                  FROM (SELECT v2.categoria_id, v2.ordem_id, v2.servico_id, v2.valor_servico,
                               v2.valor_categoria, v2.cod_serv, v2.desc_serv, v2.cod_cat,
                               v2.desc_cat, v2.qtde_serv_cat,
                               (SELECT SUM (v3.valor_servico)
                                  FROM vsdi_assistencia_ext_ordem_aut v3
                                 WHERE v3.categoria_id = v2.categoria_id
                                       AND v3.ordem_id = ".$ordem_id.") valor_servicos_somados
                          FROM (SELECT v1.categoria_id, v1.ordem_id, v1.servico_id,
                                       v1.valor_servico, v1.valor_categoria, v1.cod_serv,
                                       v1.desc_serv, v1.cod_cat, v1.desc_cat,
                                       v1.qtde_serv_cat
                                  FROM vsdi_assistencia_ext_ordem_aut v1
                                 WHERE v1.ordem_id = ".$ordem_id.") v2) v4";

        $retorno = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($retorno) ? $retorno : false;

    }


    public static function retornaValorPorOrdem($ordem_id) {

       /* $sql = 'SELECT SUM (total_valor) total_valor
                      FROM (SELECT SUM (valor) total_valor
                              FROM (SELECT DISTINCT categoria_id, valor_categoria valor
                                               FROM vsdi_assistencia_ext_ordem_aut
                                              WHERE qtde_serv_cat > 1
                                                AND ordem_id = '.$ordem_id.') tab
                            UNION ALL 
                            SELECT SUM (valor) total_valor
                              FROM (SELECT DISTINCT servico_id, valor_servico valor
                                               FROM vsdi_assistencia_ext_ordem_aut
                                              WHERE qtde_serv_cat = 1 
                                                AND ordem_id = '.$ordem_id.' ) tab) tab';*/



        $sql = "SELECT SUM (total_valor) total_valor
                      FROM (SELECT SUM (valor) total_valor
                              FROM (SELECT categoria_id,
                                           CASE
                                              WHEN valor_categoria > valor_servicos_somados
                                                 THEN valor_servicos_somados
                                              ELSE valor_categoria
                                           END valor
                                      FROM (SELECT v3.categoria_id, v3.valor_categoria,
                                                   (SELECT SUM (v2.valor_servico)
                                                      FROM vsdi_assistencia_ext_ordem_aut v2
                                                     WHERE v2.categoria_id = v3.categoria_id
                                                           AND v2.ordem_id = ".$ordem_id.") valor_servicos_somados
                                              FROM (SELECT DISTINCT v1.categoria_id, v1.valor_categoria
                                                               FROM vsdi_assistencia_ext_ordem_aut v1
                                                              WHERE v1.qtde_serv_cat > 1 AND v1.ordem_id = ".$ordem_id.") v3) ) tab
                            UNION ALL 
                            SELECT SUM (valor) total_valor
                              FROM (SELECT DISTINCT servico_id, valor_servico valor
                                               FROM vsdi_assistencia_ext_ordem_aut
                                              WHERE qtde_serv_cat = 1
                                                AND ordem_id = ".$ordem_id." ) tab) tab";

        $retorno = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($retorno['0']['total_valor']) ? $retorno['0']['total_valor'] : 0;

    }

    // Relatórios

    public  function retornaOcorrenciasPorItem($ordem_id = false, 
                                               $periodo = false, 
                                               $servico_id = false, 
                                               $categoria_id = false) {


        $sql = "SELECT cat.categoria_id, 
                       serv.servico_id,
                       atserv.cod_serv, 
                       atserv.descricao desc_serv, 
                       atcat.cod_cat,
                       atcat.descricao desc_cat, 
                       NVL(it.obser,'SEM AGRUPADOR') agrupador,
                       count(1) quantidade
                 FROM tsdi_assist_ext_aut_cat cat 
           INNER JOIN tsdi_assist_ext_aut_serv serv ON (serv.aut_cat_id = cat.ID)
           INNER JOIN tsdi_assistencia_servicos atserv ON (atserv.ID = serv.servico_id)
           INNER JOIN tsdi_assistencia_categorias atcat ON (atcat.ID = cat.categoria_id)
           INNER JOIN tsdi_assist_ext_ordens_serv ext ON (ext.ID = cat.ordem_id)
           INNER JOIN titens it ON (it.id = ext.item_id )
                WHERE EXISTS ( SELECT 1 FROM tsdi_assist_ext_status st WHERE st.ordem_id = ext.id AND ( st.status_id = 8 OR st.status_id = 10 ) )  ";
        
        if(!empty($ordem_id)){
            $sql .=" AND ext.id = ".$ordem_id;
        }

        if(!empty($servico_id)){
            $sql .=" AND serv.servico_id = ".$servico_id;
        }

        if(!empty($categoria_id)){
            $sql .=" AND cat.categoria_id = ".$categoria_id;
        }

        if (!empty($periodo) && $periodo != null) {
            $periodo = trim($periodo);
            if (strpos($periodo, '|') !== false) {
                list($inicio, $fim) = explode('|', $periodo);
                $sql .= " AND TRUNC(ext.criado_em) BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                $sql .= " AND TRUNC(ext.criado_em) = '" . $periodo . "'";
            }
        }

        $sql.=" GROUP BY cat.categoria_id, 
                      serv.servico_id,
                      atserv.cod_serv, 
                      atserv.descricao, 
                      atcat.cod_cat,
                      atcat.descricao, 
                      it.obser
                ORDER BY 1,2,7 ASC ";

        $retorno = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($retorno) ? $retorno : false;

    }

}