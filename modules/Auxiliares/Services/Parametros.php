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
namespace Lidere\Modules\Auxiliares\Services;

use Lidere\Core;
use Lidere\Models\Auxiliares;
use Lidere\Models\EmpresaParametros;
use Lidere\Modules\Services\Services;

/**
 * Parametros
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Auxiliares\Services\Parametros
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Parametros extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list()
    {
        $auxiliaresModel = new Auxiliares();
        if ( $this->usuario['sistema'] != 1 ) {
            $this->data['filtros'] = array('p.sistema' => ' = 0');
        }

        if ( isset($this->input['grupo']) && $this->input['grupo'] != null ) {
            $this->data['filtros'] = array('p.grupo' => ' = "'.$this->input['grupo'].'"');
        }

        $campos_filtro['empresas'] = Core::comboEmpresas();

        $parametros = $auxiliaresModel->parametros('result', $this->data['filtros']);

        if (!empty($parametros)) {
            foreach ( $parametros as &$parametro ) {
                $parametro['valor'] = $auxiliaresModel->parametroValor(
                    array(
                        'empresa_id = ' => $this->empresa['id'],
                        'parametro_id = ' => $parametro['id']
                    )
                );
                if ( $parametro['esconde'] == 1 ) {
                    $parametro['valor'] = Core::escondeSenha();
                }
            }
        }

        $campos_filtro['grupos'] = $auxiliaresModel->grupoParametros();
        $this->data['resultado'] = $parametros;
        $this->data['campos_filtro'] = $campos_filtro;

        return $this->data;
    }

    public function form($id = null)
    {
        $auxiliaresModel = new Auxiliares();
        $parametros = array();
        if ( !empty($id) ) {
            $parametros = $auxiliaresModel->parametros(
                'row',
                array(
                    'p.id' => ' = '.$id
                )
            );
            $parametros['valor'] =  $auxiliaresModel->parametroValor(
                array(
                    'empresa_id = ' => $this->empresa['id'],
                    'parametro_id = ' => $id
                )
            );
            if ($parametros['tipo'] == 'select') {
                $parametros['valor'] = !empty($parametros['valor']) ? explode(',', $parametros['valor']) : null;
            }
        }

        $this->data['registro'] = $parametros;

        return $this->data;
    }

    public function edit()
    {
        $auxiliaresModel = new Auxiliares();
        unset($this->input['_METHOD']);

        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;

        $id = $this->input['id'];
        unset($this->input['id']);

        $parametro = $auxiliaresModel->parametros('row', array('p.id' => ' = '.$id));
        $existe = $auxiliaresModel->parametroSetado(array('empresa_id = ' => $this->empresa['id'], 'parametro_id = ' => $id));

        if (empty($this->input['valor'])) {
            $this->input['valor'] = null;
        } else {
            if ($parametro['tipo'] == 'select') {
                $this->input['valor'] = implode(',', $this->input['valor']);
            }
        }

        if (!empty($existe)) {
            $this->input['data_edicao'] = Core::now();
            $updated = EmpresaParametros::where('parametro_id', $id)
                                        ->where('empresa_id', $this->empresa['id'])
                                        ->update($this->input);
        } else {
            $inserted = EmpresaParametros::create([
                'parametro_id' => $id,
                'empresa_id' => $this->empresa['id'],
                'valor' => $this->input['valor']
            ]);
        }

        Core::insereLog(
            $this->modulo['url'],
            'Parâmetro '.$parametro['id'].' - '.$parametro['descricao'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
            $this->usuario['id'],
            $this->empresa['id']
        );

        return 'Parâmetro <strong>'.$parametro['descricao'].'</strong> alterado com sucesso!';
    }
}
