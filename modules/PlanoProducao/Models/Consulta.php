<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.cod.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.cod.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\PlanoProducao\Models;

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
class Consulta extends Model
{
    protected $connection;

    public function __construct()
    {
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }


    public function getPlanoProducao($type = 'result', $where = false, $limit = false)
    {

        $rows = self::select("v.*")
                    ->from('vsdi_plano_prod_tela_soma v')
                    ->where(function ($query) use ($where) {
                        if (!empty($where)) {
                            foreach ($where as $column => $value) {
                                $query->whereRaw($column . " " . $value);
                            }
                        }
                    });

        if ($limit) {
            list($records, $offset) = $limit;
            $rows->skip($records)->take($offset);
        }

        if ( $type == 'result' ) {
       //   $rows->orderBy('v.data_carga', 'v.num_ordem','ASC');

          $rows = $rows->get();
        } else {
          $rows = $rows->first();
        }

        return !empty($rows) ? $rows->toArray() : null;

    }

    public function getDemandasOrdem($ordem_id)
    {

        if(empty($ordem_id)){
            return false;
        }

        $sql = "  SELECT it.cod_item, 
                         it.desc_tecnica, 
                         ax.cod_almox, 
                         ax.descricao, 
                         dm.qtde,
                         dm.id,
                         unid.cod_unid_med,
                         itest.ac_qtde_frac
                    FROM tdemandas dm 
              INNER JOIN talmoxarifados ax ON (ax.id = dm.almox_id)
              INNER JOIN titens_planejamento itpl ON (itpl.id = dm.itpl_id)
              INNER JOIN titens_empr ite ON (ite.id = itpl.itempr_id )
              INNER JOIN titens it ON (it.id = ite.item_id) 
              INNER JOIN titens_estoque itest ON (itest.itempr_id = ite.id)
              INNER JOIN tunid_med unid ON (unid.id = itest.unid_med_id)
                   WHERE dm.ordem_id = {$ordem_id} 
                ORDER BY dm.id ASC";

       
        $demandas = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($demandas) ? $demandas : false;

    }
}