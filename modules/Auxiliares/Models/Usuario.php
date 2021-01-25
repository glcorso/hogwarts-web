<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * PHP Version 7
 *
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2018
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\Auxiliares\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Classe para consulta, inclusão, edição e exclusão do usuários
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Auxiliares\Models\Usuario
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Usuario  extends Model
{
    protected $core;

    public $table = 'tusuarios';

    public $timestamps = false;

    public function __construct() {
        $this->core = Core::getInstance();
    }

    public function buscaUsuarios($restricao = false) {

        $sql = "SELECT * FROM ".$this->table;
        $where = "";
        if ( $restricao ) {
            foreach ( $restricao as $column => $value ) {
                foreach ( $value as $col => $val ) {
                    $where .= empty($where) ? " WHERE " : " AND ";
                    $where .= $col .  " = '".$val."'";
                }
            }
            $sql .= $where;
        }
        $sql .= " ORDER BY nome ASC";
        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $r;

    }

    public function buscaUsuario($restricao = false) {

        if ( !$restricao ) {
            return false;
        }

        $sql = "SELECT * FROM ".$this->table;
        $where = "";
        foreach ( $restricao as $coluna => $valor ) {
            $where .= empty($where) ? " WHERE" : " AND";
            $where .= " `" . $coluna ."` = '".$valor."'";
        }
        $sql .= $where;
        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    // public function getUserByEmail($email = false){

    //  $sql = "SELECT * FROM ".$this->table." WHERE email = :email";
    //  $query = $this->core->db->prepare($sql);
    //  $query->bindValue(":email", $email);

    //  $r = array();
    //  if ($query->execute()) {
    //      $r = $query->fetch(PDO::FETCH_ASSOC);
    //  }

    //  return $r;
    // }


    public function buscaUsuariosEmpresa( $exists = false) {

        $sql = "SELECT * FROM ".$this->table;
        $where = "";


        if ($exists) {
            $sql .= $exists;
        }
        $sql .= " ORDER BY nome ASC";


        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $r;

    }


    public function getInternalUsers() {

        $sql = "SELECT * FROM ".$this->table." WHERE situacao = :situacao AND tipo = :tipo ORDER BY nome";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':situacao', 'ativo');
        $query->bindValue(':tipo', 'interno');


        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $r = 0;
        }

        return $r;

    }

    public function getUsers($company_id = false, $search = false) {

        $sql = "SELECT distinct u.*, 
                       u.nome name,
                       Date_format(u.data_edicao, '%d/%m/%Y %H:%i') last_login_br, 
                       c.name                                       company_name 
                FROM   tusuarios u, 
                       tusuarios_company cu, 
                       company c 
                WHERE  c.id = cu.company_id 
                   AND cu.usuario_id = u.id ";

        if($company_id){
            $sql .= " AND cu.company_id = ".$company_id;
        }

        if($search){
            foreach ( $search as $column => $value ) {
                foreach ( $value as $col => $val ) {
                    if ( is_array($val) ) {
                        $sql .= " AND (";
                        $c = 1;
                        foreach ( $val as $col2 => $val2 ) {
                            if ( $c > 1 ) {
                                $sql .= "OR";
                            }
                            $sql .= " " . $col2 . " LIKE '%" . $val2 . "%' ";
                            $c++;
                        }
                        $sql .= ")";
                    } else {
                        $sql .= " AND " . $col .  " = '".$val."'";
                    }
                }
            }
        }

        $sql .= " ORDER BY u.nome ASC";

        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $r = 0;
        }
        return $r;

    }

    public function getAdminUsers() {

        $sql = "SELECT * FROM user WHERE status = :status  ORDER BY name";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':status', 'ON');
       // $query->bindValue(':administrator', 'ON');

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $r = 0;
        }
        return $r;

    }

    public function getAdminUsersByAttendance($search) {

        $sql = "SELECT *
                  FROM user
                 WHERE status = :status
                   AND administrator = :administrator ";

        $where = "";
        if ($search) {
            foreach ($search as $coluna => $valor) {
                $where .= ' AND ' . $coluna . " " . $valor;
            }
            $sql .= $where;
        }

        $sql.= " ORDER BY name";

        $query = $this->core->db->prepare($sql);
        $query->bindValue(':status', 'ON');
        $query->bindValue(':administrator', 'ON');

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $r = 0;
        }
        return $r;

    }
}
