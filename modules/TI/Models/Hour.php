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
class Hour {

	protected $core;

	function __construct() {
		$this->core = Core::getInstance();
		$this->table = 'hour';
	}

	public function getHours($status = false) {

		$where = "";
		if ( $status ) {
			$where = " WHERE status = '".$status."'";
		}

		$sql = "SELECT * FROM ".$this->table." ".$where." ORDER BY name";
		$query = $this->core->db->prepare($sql);

		$r = array();
		if ($query->execute()) {
			$r = $query->fetchAll(PDO::FETCH_ASSOC);
		} else {
			$r = 0;
		}
		return $r;

	}

	public function getHour($id) {

		$sql = "SELECT * FROM ".$this->table." WHERE id = :id";
		$query = $this->core->db->prepare($sql);
		$query->bindValue(':id', $id);

		$r = array();
		if ($query->execute()) {
			$r = $query->fetch(PDO::FETCH_ASSOC);
		} else {
			$r = 0;
		}
		return $r;

	}

	public function getHourByCompanyId($hour_id, $company_id) {
		$sql = "SELECT * FROM `company_hours` WHERE company_id = :company_id AND hour_id = :hour_id";
		$query = $this->core->db->prepare($sql);
		$query->bindValue(':company_id', $company_id);
		$query->bindValue(':hour_id', $hour_id);

		$r = array();
		if ($query->execute()) {
			$r = $query->fetch(PDO::FETCH_ASSOC);
		} else {
			$r = 0;
		}
		return $r;
	}

}