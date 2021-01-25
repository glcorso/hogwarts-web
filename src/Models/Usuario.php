<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Classe para consulta, inclusão, edição e exclusão das empresas do sistema
 *
 * @package Models
 * @category Model
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Usuario extends Model
{
    public $table = 'tusuarios';

    //$usuario = \Lidere\Models\Usuario::find(2);
    //$usuario->modulo()->get()->toArray();
    public function modulo()
    {
        return $this->belongsToMany('Lidere\Models\Modulo', 'tmodulos_usuarios')
                    ->withPivot('empresa_empr_id', 'permissao');
    }

    public function SetorUsuario()
    {
    	return $this->hasOne(SetorUsuario::class);
    }

    public static function retornaUsuarioAssitencias($string)
    {

        $sql = "tipo = 'ATE'";
        if (!empty($string)) {
            $sql .= " AND (upper(nome) like upper('%{$string}%') OR upper(usuario) like upper('%{$string}%') ) ";
        }
        $usarios = Usuario::whereRaw($sql)
            ->get()
            ->toArray();

        return $usarios;
    }

    public static function auth($http_x_usuario = null, $http_x_empresa = null)
    {
        // Efetua a autenticação atraves do token do usuário e empresa
        $user = Usuario::select(
            'tusuarios.id',
            'tusuarios.nome',
            'tusuarios.tipo',
            'tusuarios.email',
            'tusuarios.situacao',
            'tusuarios.data_criacao',
            'tusuarios.data_edicao',
            'tusuarios.sistema',
            'tusuarios.empresa_id AS empresa_padrao_id',
            'tusuarios.ad',
            'tusuarios.token',
            'e.id AS empresa_id',
            'e.razao_social',
            'e.nome_fantasia',
            'e.dominio',
            'e.diretorio',
            'e.cor_principal',
            'e.api_token AS token_empresa',
            'e.empr_id'
        )
            ->leftJoin(
                'tmodulos_usuarios AS mu',
                'mu.usuario_id',
                '=',
                'tusuarios.id'
            )
            ->join(
                'tempresas AS e',
                function ($join) {
                    $join->on(
                        'e.id',
                        '=',
                        'mu.empresa_empr_id'
                    )->orOn(
                        'e.id',
                        '=',
                        'tusuarios.empresa_id'
                    );
                }
            )
            ->where('tusuarios.situacao', 'ativo')
            ->where('e.situacao', 'ativo')
            ->where('tusuarios.token', $http_x_usuario)
            ->where('e.api_token', $http_x_empresa)
            ->first();
        return $user;
    }


    public function perfil()
    {
        return $this->belongsTo('Lidere\Modules\Auxiliares\Models\Perfil');
    }


}
