<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Api\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Usuario
 *
 * @package Lidere\Modules
 * @subpackage Api\Models
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Usuario extends Model
{

    public $table = 'tusuarios';
    public $timestamps = false;

    protected $fillable = [
          'nome'
        , 'senha'
        , 'usuario'
        , 'situacao'
        , 'token'
        , 'email'
        , 'tipo'
        , 'empresa_id'
    ];

    protected $hidden = [
        'senha'
    ];

    public static function criar(\stdClass $input = null)
    {

        $usuario = null;

        try {

            $usuario = self::create([
                  'nome'       => $input->nome
                , 'usuario'    => $input->usuario
                , 'situacao'   => $input->situacao
                , 'email'      => $input->email
                , 'token'      => $input->token
                , 'senha'      => Core::geraSenha($input->senha)
                , 'empresa_id' => $input->empresa_id
            ]);

        } catch(\Exception $e) {
            dlog('error', $e->getMessage());

            throw new \Exception($e->getMessage());
        }

        return $usuario;
        
    }

    /**
     * Atualiza
     *
     * @param Usuario|null $find
     * @param \stdClass|null $usuario
     * @return bool|int
     * @throws \Exception
     */
    public static function atualizar(Usuario $find = null, \stdClass $usuario = null)
    {
        $updated = false;

        try {

            $find->update([
                  'nome'       => $usuario->nome
                , 'usuario'    => $usuario->usuario
                , 'situacao'   => $usuario->situacao
                , 'email'      => $usuario->email
            ]);

            $updated = true;

        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }

        return $updated;

    }

    public static function atualizarSenha(Usuario $find = null, string $novaSenha)
    {
        $updated = false;

        try {

            $find->update([
                'senha' => $novaSenha
            ]);

            $updated = true;

        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }

        return $updated;

    }

    public static function excluir($id = null)
    {
        $deleted = false;

        try {

            $deleted = self::where('id', $id)
                           ->delete();

        } catch (\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }

        return $deleted;

    }

}
