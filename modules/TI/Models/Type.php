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
class Type {

	protected $core;

	function __construct() {
		$this->core = Core::getInstance();
		$this->table = 'type';
	}

	public function getTypes() {

		$sql = "SELECT * FROM ".$this->table." ORDER BY name ASC";
		$query = $this->core->db->prepare($sql);

		$r = null;
		if ($query->execute()) {
			$r = $query->fetchAll(PDO::FETCH_ASSOC);
		}

		return $r;

	}

	public function getType($id) {

		$sql = "SELECT * FROM ".$this->table." WHERE id = :id";
		$query = $this->core->db->prepare($sql);
		$query->bindValue(':id', $id);

		$r = null;
		if ($query->execute()) {
			$r = $query->fetch(PDO::FETCH_ASSOC);
		}
		return $r;

	}

}