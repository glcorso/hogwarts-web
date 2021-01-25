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
 * Classe para consulta, inclusão, edição e exclusão
 *
 * @author Ramon Barros
 * @package Lidere\Modules\TI
 * @category Models
 */
class DevelopmentProject {

    protected $core;

    function __construct() {
        $this->core = Core::getInstance();
        $this->table = 'development_project';
    }

    public function getProjects() {

        $sql = "SELECT d.id,
                       d.description,
                       d.type_project,
                       d.project_id,
                       d.note,
                       d.status,
                       d.user_analyst,
                       DATE_FORMAT(d.date_start, '%d/%m/%Y') date_start,
                       DATE_FORMAT(d.date_end, '%d/%m/%Y') date_end,
                       d.created_by,
                       d.created_at,
                       d.modified_by,
                       d.modified_at,
                       d.total_hours,
                       u.name analyst_name
                 FROM ".$this->table." d, user u
                WHERE u.id = d.user_analyst
                ORDER BY id DESC";
        $query = $this->core->db->prepare($sql);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $r;

    }

    public function getProjectById($id) {

        $sql = "SELECT * FROM ".$this->table." WHERE id = :id";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':id', $id);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }

        return $r;

    }

    public function getDevProjectById($id) {

        $sql = "SELECT de.* FROM development_project_dev de WHERE de.development_project_id = :id ORDER BY de.id ASC";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':id', $id);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $r;

    }


    public function getTicketEstimateProjectById($id)
    {
        $sql = "SELECT t.*, DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') created_at_br, c.id company_id, c.name company_name, u.name user_name, tp.name type_name, pr.name priority_name" .
               "  FROM ticket t, project p, company c, user u, type tp, priority pr , development_project_estimate est" .
               " WHERE pr.id = t.priority_id" .
               "   AND tp.id = t.type_id" .
               "   AND u.id = t.user_id" .
               "   AND c.id = p.company_id" .
               "   AND p.id = t.project_id" .
               "   AND est.development_project_id = {$id}" .
               "   AND est.ticket_estimate = t.id ";
        $query = $this->core->db->prepare($sql);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $r;
    }


    public function getDevProjectByUser($id, $id_project, $date) {

        $sql = "SELECT de.*
                FROM development_project_dev de
                  WHERE de.user_development = :id
                  AND de.development_project_id = :id_project
                  AND de.date = DATE_FORMAT(\"$date\", \"%Y-%m-01\")
                ORDER BY de.id ASC";

        $query = $this->core->db->prepare($sql);

        $query->bindValue(':id', $id);
        $query->bindValue(':id_project', $id_project);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }

        return $r;

    }



}