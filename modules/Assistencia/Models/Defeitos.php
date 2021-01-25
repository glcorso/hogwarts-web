<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.cod.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.cod.br)
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
class Defeitos extends Model
{
    protected $connection;

    public function __construct()
    {
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }


    public function getDefeitos($type = 'result', $where = false, $limit = false)
    {

        $rows = self::select("d.*")
                    ->from('tsdi_assistencia_defeitos d')
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
          $rows->orderBy('TO_NUMBER(d.cod_defeito)','ASC');

          $rows = $rows->get();
        } else {
          $rows = $rows->first();
        }



        return !empty($rows) ? $rows->toArray() : null;

    }

    public function cadastrarDefeito($defeito)
    {

        if ( empty($defeito) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            INSERT INTO tsdi_assistencia_defeitos
            (id, cod_defeito, descricao, situacao, criado_por, criado_em)
            VALUES (seq_id_tsdi_assist_defeitos.nextval, '{$defeito['cod_defeito']}', '{$defeito['descricao']}', '{$defeito['situacao']}', '{$_SESSION['usuario']['id']}' ,SYSDATE);
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            $id = $pdo->lastInsertId('seq_id_tsdi_assist_defeitos');
            return $id;
        }

    }

    public function alterarDefeito($defeito)
    {

        if ( empty($defeito) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            UPDATE tsdi_assistencia_defeitos
                  SET   cod_defeito   = '{$defeito['cod_defeito']}'
                      , descricao    = '{$defeito['descricao']}'
                      , situacao     = '{$defeito['situacao']}'
                      , alterado_por = '{$_SESSION['usuario']['id']}'
                      , alterado_em  = SYSDATE
            WHERE id = {$defeito['id']};
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            return true;
        }

    }

    public function deletarDefeito($defeito_id)
    {

        if ( empty($defeito_id) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            DELETE 
              FROM tsdi_assistencia_defeitos
             WHERE id = {$defeito_id};
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            return true;
        }

    }

    public function retornaDefeitosSelect2($string)
    {

        $sql = " SELECT d.id, d.cod_defeito codigo, d.descricao
                   FROM tsdi_assistencia_defeitos d
                  WHERE d.situacao = 1 ";
        if(!empty($string)){
            $sql .= " AND (upper(d.cod_defeito) like upper('%{$string}%') OR upper(d.descricao) like upper('%{$string}%') ) ";
        }
        $sql .= " ORDER BY to_number(d.cod_defeito) ASC";

        $defeitos = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($defeitos) ? $defeitos : false;
    
    }

}