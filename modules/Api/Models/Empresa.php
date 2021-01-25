<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Api\Models;

use PDO;
use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\Api\Models\EmpresaErp;
use Lidere\Modules\Api\Models\EmpresaConector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Empresa
 *
 * @package Lidere\Modules
 * @subpackage Api\Models
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class Empresa extends Model
{

    public $table = 'tempresas';
    public $timestamps = false;

    protected $fillable = [
        // 'id',
        // 'razao_social',
        // 'nome_fantasia',
        // 'empr_id'
        'campos_obrigatorios'
    ];


    public function Parametro()
    {
        return $this->belongsToMany(Parametro::class, 'tempresa_parametros', 'empresa_id', 'parametro_id')->withPivot('valor');
    }


    /**
     * Atualiza
     *
     * @param Empresa|null $find
     * @param \stdClass|null $empresa
     * @return bool|int
     * @throws \Exception
     */
    public static function atualizar(Empresa $find = null, \stdClass &$empresa = null)
    {
        $updated = false;

        try {
            $find->update([
                  'campos_obrigatorios'  => $empresa->campos_obrigatorios
            ]);
            $updated = true;
        } catch (\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $updated;
    }
}
