<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Assistencia\Models;

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
class Motivos extends Model
{
    protected $connection;

    public function __construct()
    {
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }


    public function getMotivos($type = 'result', $where = false, $limit = false)
    {

        $rows = self::select("m.*")
                    ->from('tsdi_assistencia_motivos m')
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
          $rows->orderBy('TO_NUMBER(m.cod_motivo)','ASC');

          $rows = $rows->get();
        } else {
          $rows = $rows->first();
        }



        return !empty($rows) ? $rows->toArray() : null;

    }

    public function cadastrarMotivo($motivo)
    {

        if ( empty($motivo) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            INSERT INTO tsdi_assistencia_motivos
            (id, cod_motivo, descricao, situacao, criado_por, criado_em, defeito_obrigatorio)
            VALUES (seq_id_tsdi_assist_motivos.nextval, '{$motivo['cod_motivo']}', '{$motivo['descricao']}', '{$motivo['situacao']}', '{$_SESSION['usuario']['id']}' ,SYSDATE,  '{$motivo['defeito_obrigatorio']}');
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            $id = $pdo->lastInsertId('seq_id_tsdi_assist_motivos');
            return $id;
        }

    }

    public function alterarMotivo($motivo)
    {

        if ( empty($motivo) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            UPDATE tsdi_assistencia_motivos
                  SET   cod_motivo   = '{$motivo['cod_motivo']}'
                      , descricao    = '{$motivo['descricao']}'
                      , situacao     = '{$motivo['situacao']}'
                      , defeito_obrigatorio     = '{$motivo['defeito_obrigatorio']}'
                      , alterado_por = '{$_SESSION['usuario']['id']}'
                      , alterado_em  = SYSDATE
            WHERE id = {$motivo['id']};
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            return true;
        }

    }

    public function deletarMotivo($motivo_id)
    {

        if ( empty($motivo_id) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            DELETE 
              FROM tsdi_assistencia_motivos
             WHERE id = {$motivo_id};
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            return true;
        }

    }

    public function retornaMotivosSelect2($string)
    {

        $sql = " SELECT m.id, m.cod_motivo codigo, m.descricao
                   FROM tsdi_assistencia_motivos m
                  WHERE m.situacao = 1 ";
        if(!empty($string)){
            $sql .= " AND (upper(m.cod_motivo) like upper('%{$string}%') OR upper(m.descricao) like upper('%{$string}%') ) ";
        }
        $sql .= " ORDER BY TO_NUMBER(m.cod_motivo) ASC";

        $motivos = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($motivos) ? $motivos : false;
    
    }

}