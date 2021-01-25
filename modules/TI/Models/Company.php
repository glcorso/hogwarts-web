<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2016  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\TI\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Classe para consulta, inclusão, edição e exclusão de tabelas padrões do sistema
 *
 * @author Sergio Sirtoli Jr.
 * @package Models
 * @category Core
 */
class Company {

    protected $core;

    function __construct() {
        $this->core = Core::getInstance();
        $this->table = 'company';
    }

    public function getCompanies($search = false) {

        $where = null;
        if ( $search ) {
            $where .= "WHERE (name LIKE '%".$search."%' OR corporate_name LIKE '%".$search."%')";
        }

        $sql = "SELECT * FROM ".$this->table." ".$where." ORDER BY name ASC";
        $query = $this->core->db->prepare($sql);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $r;

    }

    public function getCompany($id) {

        $sql = "SELECT * FROM ".$this->table." WHERE id = :id";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':id', $id);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }
        return $r;

    }

    public function getCompanyByUserId($id) {

        $sql = "SELECT c.* FROM ".$this->table." c, user u WHERE u.company_id = c.id AND u.id = :id";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':id', $id);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $r;

    }

}