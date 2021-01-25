<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.com.br)
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
class Vinculo extends Model
{
    protected $connection;

    public function __construct()
    {
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }


    public function getVinculos($type = 'result', $where = false, $limit = false)
    {

        $rows = self::select("i.*","tf.cod_func","tf.nome descricao")
                    ->from('tsdi_vinc_usu_planejador  i')
                    ->join('tfuncionarios  tf','tf.id','=','i.func_id')
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
          $rows->orderBy('TO_NUMBER(i.id)','ASC');

          $rows = $rows->get();
        } else {
          $rows = $rows->first();
        }



        return !empty($rows) ? $rows->toArray() : null;

    }

    public function cadastrarVinculo($vinculo)
    {

        if ( empty($vinculo) ) {
            return false;
        }

        try {

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "
            BEGIN
                INSERT INTO tsdi_vinc_usu_planejador
                (id, usuario_id, func_id, criado_por, criado_em )
                VALUES (seq_id_tsdi_vinc_usu_planej.nextval, '{$vinculo['usuario_id']}', '{$vinculo['func_id']}', '{$_SESSION['usuario']['id']}', SYSDATE);
            END;";

            $stmt = $pdo->prepare($sql);

          //  var_dump($stmt);die;

            if ( !$stmt->execute() ) {
                return false;
            } else {
                $id = $pdo->lastInsertId('seq_id_tsdi_vinc_usu_planej');
                return $id;
            }
            
        } catch (\PDOException $e) {
            return false;
        }

       

    }

    public function alterarVinculo($vinculo)
    {

        if ( empty($vinculo) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            UPDATE tsdi_vinc_usu_planejador
                  SET   usuario_id   = '{$vinculo['usuario_id']}'
                      , func_id      = '{$vinculo['func_id']}'
                      , alterado_por = '{$_SESSION['usuario']['id']}'
                      , alterado_em  = SYSDATE
            WHERE id = {$vinculo['id']};
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            return true;
        }

    }

    public function deletarVinculo($vinculo_id)
    {

        if ( empty($vinculo_id) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            DELETE 
              FROM tsdi_vinc_usu_planejador
             WHERE id = {$vinculo_id};
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            return true;
        }

    }

    public function retornaPlanejadores($id = false)
    {

        $sql = " SELECT tf.id, tf.cod_func codigo, tf.nome descricao
                   FROM tfuncionarios tf
                  WHERE tf.sit = 1  
                    AND tf.cod_func LIKE 'P%' ";

        if(!empty($id)){

            $sql .= " AND tf.id = {$id}";
        }
        $sql .= " ORDER BY tf.cod_func ASC";

        $planejadores = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($planejadores) ? $planejadores : false;
    
    }
}