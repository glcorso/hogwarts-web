<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.cod.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.cod.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Relatorios\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Classe para consulta, inclusão, edição e exclusão do ERP
 *
 * @author Sergio Sirtoli Jr.
 * @package Models
 * @category Model
 */
class EstruturaProduto extends Model
{
    protected $connection;

    public function __construct()
    {
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }


    public function retornaItensPn($string,$itempr_id)
    {

        $and = !empty($itempr_id) ? " AND id = ".$itempr_id : '';

        $sql = "SELECT * FROM (SELECT DISTINCT itempr_id id, 
				        cod_item codigo, 
				        decode(pn,null, desc_tecnica|| DECODE(tmasc_item_id,null,'','Máscara: '||tmasc_item_id) , (desc_tecnica || DECODE(tmasc_item_id,null,'',' - Máscara: '||tmasc_item_id)|| ' - PN: '||PN||' - Cliente: '||COD_CLI||' - '||DESCRICAO )) DESCRICAO , tmasc_item_id mascara_id, cod_cli, descricao desc_cli , pn, desc_tecnica FROM 
				(SELECT DISTINCT itcm.itempr_id, 
				                 it.cod_item, 
				                 it.desc_tecnica, 
				                 NVL (cliit.cod_clidesc_item, cliit.desc_clidesc_item) pn, 
				                 est.cli_id, 
				                 est.id est_id, 
				                 cli.cod_cli,  
				                 est.descricao,
				                 cliit.tmasc_item_id
				  FROM titens it INNER JOIN titens_empr ite ON (ite.item_id = it.ID)
				       INNER JOIN titens_comercial itcm ON (itcm.itempr_id = ite.ID)
				       LEFT JOIN tclidesc_itens cliit ON (cliit.item_id = it.ID)
				       LEFT JOIN testabelecimentos est ON (est.ID = cliit.est_id)
				       LEFT JOIN testab_dados_fat estfat ON (est.ID = estfat.est_id) 
				       LEFT JOIN tclientes cli ON (cli.id = est.cli_id)
				 WHERE ite.empr_id = 1) )
				 WHERE 1 =1 ";
        if(!empty($string)){
            $sql .= " AND (upper(codigo) like upper('%{$string}%') OR upper(descricao) like upper('%{$string}%') ) ";
        }
        $sql .= "{$and}";

        $itens = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        if(!empty($itempr_id)){
            return !empty($itens['0']) ? $itens['0'] : false;
        }else{
            return !empty($itens) ? $itens : false;
        }
    }


    public function retornaEstruturaItem($itempr_id, $mascara_id, $custo)
    {

    	$mascara_id = !empty($mascara_id) ? $mascara_id : 'NULL';

        $sql = "SELECT * FROM (SELECT  te.id itempr_id
	                  , ti.id item_id
	                  , ti.cod_item
	                  , ti.desc_tecnica
	                  , tu.cod_unid_med unid_med
	                  , tgrp.cod_grp_ite cod_grp
	                  , fisc.nbm ncm
	                  , tc.origem
	                  , estrutura.qtde_corrigida
	                  , estrutura.nivel
	                  , estrutura.ordenacao
	                  , DECODE({$custo}, 0, 0, ROUND(( ( (NVL((select cus.vlr_cst_dir from titens_custos cus where cus.itempr_id = te.id),0) * estrutura.qtde_corrigida) / {$custo}) * 100),2)) custo_mat
	              FROM TABLE ( FSDI_RETORNA_ESTRUTURA (     1
	                                                      , {$itempr_id}
	                                                      , {$mascara_id}
	                                                      , pi_data             => TRUNC(SYSDATE)
	                                                      , pi_ind_tipo_item    => 'T' 
	                                                      , pi_retorna_fantasma => 'F'
	                                                      , pi_retorna_pai_type => 'S'
	                                                      )
	                            ) estrutura
	                    , titens_empr te
	                    , titens ti
	                    , titens_estoque tes
	                    , tunid_med tu
	                    , tgrp_clas_ite tgrp
	                    , titens_contabil tc
	                    , tclas_fisc fisc
	              WHERE estrutura.itempr_id = te.id 
	                 and ti.id = te.item_id
	                 and te.id = tes.itempr_id
	                 and tes.unid_med_id = tu.id
	                 and tgrp.id = tes.grp_clas_id
	                 and tc.itempr_id = te.id 
	                 and tc.clas_fisc_id = fisc.id
	                 and nivel > 0) 
	                 ORDER BY custo_mat DESC ";

	   // var_export($sql);die;





        $estrutura = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($estrutura) ? $estrutura : false;
    }


    public function retornaCustoItem($itempr_id)
    {

        $sql = " SELECT ROUND(vlr_cst_dir,4) custo FROM titens_custos where itempr_id = {$itempr_id}";

       	$custo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($custo['0']) ? $custo['0'] : false;
    }
}