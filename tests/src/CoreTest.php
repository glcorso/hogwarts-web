<?php

use Lidere\Core;
use Lidere\Models\Aplicacao;
use Lidere\Models\Modulo;

/**
 * Teste da Classe Core
 */
class CoreTest extends AbstractTest
{
    private $instance;

    public static function setUpBeforeClass()
    {

    }

    public static function tearDownAfterClass()
    {

    }

    public function assertPreConditions()
    {
        $this->assertTrue(
            class_exists($class = 'Lidere\\Core'),
            'Class not found: '.$class
        );
    }

    public function testeInicial()
    {
        $this->assertTrue(true);
    }

    public function testeModelModulo()
    {
        $aplicacaoObj = new Aplicacao();

        $filtro = array();
        //select * from `tmodulos` as `m` order by `m`.`ordem` asc
        $modulos1 = $aplicacaoObj->buscaModulosSistema($filtro);

        $modulos2 = Modulo::orderBy('ordem', 'asc')
                          ->get()
                          ->toArray();

        $this->assertEquals($modulos1, $modulos2);

        $filtro = array();
        $filtro['m.modulo_id'] = ' = 1';
        // select * from `tmodulos` as `m` where (m.modulo_id  = 1) order by `m`.`ordem` asc
        $modulos3 = $aplicacaoObj->buscaModulosSistema($filtro);


        $modulos4 = Modulo::where('modulo_id', 1)
                          ->orderBy('ordem', 'asc')
                          ->get()
                          ->toArray();

        $this->assertEquals($modulos3, $modulos4);

        $filtro = array();
        $filtro['m.modulo_id'] = ' IS NULL';
        // select * from `tmodulos` as `m` where (m.modulo_id  IS NULL) order by `m`.`ordem` asc
        $modulos5 = $aplicacaoObj->buscaModulosSistema($filtro);

        $modulos6 = Modulo::whereNull('modulo_id')
                          ->orderBy('ordem', 'asc')
                          ->get()
                          ->toArray();

        $this->assertEquals($modulos5, $modulos6);

        $modulo_id = 1;
        $modulos7 = Modulo::where(function ($query) use ($modulo_id) {
                            if (!empty($modulo_id)) {
                                $query->where('modulo_id', $modulo_id);
                            } else {
                                $query->whereNull('modulo_id');
                            }
                          })
                          ->orderBy('ordem', 'asc')
                          ->get()
                          ->toArray();

        $this->assertEquals($modulos3, $modulos7);
    }

    public function testeModelModuloUsuario()
    {
        $aplicacaoObj = new Aplicacao();
        // SELECT `m`.*,
        //        `mu`.`permissao`,
        //        `mu`.`empresa_empr_id`
        // FROM `tmodulos` AS `m`
        // INNER JOIN `tmodulos_usuarios` AS `mu` ON `mu`.`modulo_id` = `m`.`id`
        // INNER JOIN `tusuarios` AS `u` ON `u`.`id` = `mu`.`usuario_id`
        // WHERE (m.menu = "S"
        //        AND m.id = 5
        //        AND mu.usuario_id = 1
        //        AND mu.empresa_empr_id = 1)
        //
        // $usuario = \Lidere\Models\Usuario::find(1);
        // dd($usuario->modulo()->select(
        //     'tmodulos.*',
        //     'tmodulos_usuarios.permissao',
        //     'tmodulos_usuarios.empresa_empr_id'
        //     )->whereMenu('S')->whereEmpresaEmprId(1)->toSql());

        $permissao1 = $aplicacaoObj->buscaModuloUsuario(
            array(
                'm.id' => ' = 1',
                'u.id' => ' = 2'
            )
        );

        $usuario = \Lidere\Models\Usuario::find(2);
        $permissao2 = $usuario->modulo()
                              ->select(
                                'tmodulos.*',
                                'tmodulos_usuarios.permissao',
                                'tmodulos_usuarios.empresa_empr_id'
                             )
                            ->whereId(1)
                            ->first();

        $permissao2 = !empty($permissao2) ? $permissao2->toArray() : array();
        unset($permissao2['pivot']);

        $this->assertEquals($permissao1, $permissao2);
    }
}
