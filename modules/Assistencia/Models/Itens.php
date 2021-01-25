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
class Itens extends Model
{
    protected $connection;

    public function __construct()
    {
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }


    public function getItens($type = 'result', $where = false, $limit = false)
    {

        $rows = self::select("i.*")
                    ->from('tsdi_assistencia_itens  i')
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

    public function cadastrarItem($item)
    {

        if ( empty($item) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            INSERT INTO tsdi_assistencia_itens
            (id, descricao, situacao, criado_por, criado_em )
            VALUES (seq_id_tsdi_assist_itens.nextval, '{$item['descricao']}', '{$item['situacao']}', '{$_SESSION['usuario']['id']}', SYSDATE);
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            $id = $pdo->lastInsertId('seq_id_tsdi_assist_itens');
            return $id;
        }

    }

    public function alterarItem($item)
    {

        if ( empty($item) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            UPDATE tsdi_assistencia_itens
                  SET   descricao    = '{$item['descricao']}'
                      , situacao     = '{$item['situacao']}'
                      , alterado_por = '{$_SESSION['usuario']['id']}'
                      , alterado_em  = SYSDATE
            WHERE id = {$item['id']};
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            return true;
        }

    }

    public function deletarItens($item_id)
    {

        if ( empty($item_id) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            DELETE 
              FROM tsdi_assistencia_itens
             WHERE id = {$item_id};
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            return true;
        }

    }
}