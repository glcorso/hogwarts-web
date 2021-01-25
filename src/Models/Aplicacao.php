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
 * Classe para consulta, inclusão, edição e exclusão de tabelas padrões do sistema
 *
 * @category Core
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Aplicacao
{
    /**
     * Busca os modulos do sistema
     * @param  array $restricao   Criterio de busca
     * @return array              Retorna os modulos
     */
    public function buscaModulosSistema(array $restricao = array()): array
    {
        $modulos = DB::table('tmodulos AS m')
                   ->where(function ($query) use ($restricao) {
                    if (!empty($restricao)) {
                        foreach ($restricao as $coluna => $valor) {
                            $query->whereRaw($coluna." ".$valor);
                        }
                    }
                   })
                   ->orderBy('m.ordem')
                   ->get();

        return !empty($modulos) ? $modulos : array();
    }

    /**
     * Busca os modulos relacionados ao usuário
     * @param  array $restricao Criterio de busca
     * @return array            Retorna os modulos relacionados ao usuário
     */
    public function buscaModulos(array $restricao = array()): array
    {
        $modulos = DB::table('tmodulos AS m')
                     ->select('m.*')
                     ->join('tmodulos_usuarios AS mu', 'mu.modulo_id', '=', 'm.id')
                     ->join('tusuarios AS u', 'u.id', '=', 'mu.usuario_id')
                     ->where(function ($query) use ($restricao) {
                        if (!empty($restricao)) {
                            foreach ($restricao as $coluna => $valor) {
                                $query->whereRaw($coluna.$valor);
                            }
                        }
                     })
                     ->orderBy('m.ordem')
                     ->get();

        return !empty($modulos) ? $modulos : array();
    }

    /**
     * Busca um modulo
     * @param  array $restricao Critério de busca
     * @return array            Retorna os dados do modulo
     */
    public function buscaModulo(array $restricao = array()): array
    {
        $modulo = DB::table('tmodulos AS m')
                    ->select(
                        array(
                                'm.*',
                                'm2.nome AS nome_pai',
                                'm2.icone AS icon_pai',
                                'm2.url AS url_pai'
                            )
                    )
                      ->leftJoin('tmodulos AS m2', 'm2.id', '=', 'm.modulo_id')
                      ->where(function ($query) use ($restricao) {
                        if (!empty($restricao)) {
                            foreach ($restricao as $coluna => $valor) {
                                $query->where($coluna, '=', $valor);
                            }
                        }
                      })
                      ->first();

        return !empty($modulo) ? $modulo : array();
    }

    /**
     * Busca um modulo relacionado ao usuário
     * @param  array $restricao Critério de busca
     * @return array            Retorna os dados do modulo
     */
    public function buscaModuloUsuario(array $restricao = array()): array
    {
        if (!$restricao) {
            return false;
        }

        $moduloUsuario = DB::table('tmodulos AS m')
                           ->select(
                               array(
                                    'm.*',
                                    'mu.permissao',
                                    'mu.empresa_empr_id'
                                )
                           )
                            ->join('tmodulos_usuarios AS mu', 'mu.modulo_id', '=', 'm.id')
                            ->join('tusuarios AS u', 'u.id', '=', 'mu.usuario_id')
                            ->where(function ($query) use ($restricao) {
                                if (!empty($restricao)) {
                                    foreach ($restricao as $coluna => $valor) {
                                        $query->whereRaw($coluna.$valor);
                                    }
                                }
                            })
                            ->first();

        return !empty($moduloUsuario) ? $moduloUsuario : array();
    }

    /**
     * Exibe o script sql para debug
     * @param  string $table Nome da tabela
     * @param  array  $data   Colunas e valores
     * @param  array  $criteria Coluna e valor para busca
     * @param  string $type  Tipo de query insert/update
     * @return array         Array contendo as informações da query
     */
    public function debug(
        string $table = '',
        array $data = array(),
        array $criteria = array(),
        string $type = 'insert',
        string $operator = '='
    ): array {
        $builder = DB::table($table);
        $grammar = $builder->getGrammar();

        if ($type == 'insert') {
            $sql = $grammar->compileInsertGetId($builder, $data, null);
        } elseif ($type == 'update') {
            $builder->where(function ($query) use ($criteria, $operator) {
                if (!empty($criteria)) {
                    foreach ($criteria as $coluna => $valor) {
                        if ($operator == false) {
                            $query->whereRaw($coluna.$valor);
                        } else {
                            $query->where($coluna, $operator, $valor);
                        }
                    }
                }
            });
            $sql = $grammar->compileUpdate($builder, $data);
        } elseif ($type == 'delete') {
            $builder->where(function ($query) use ($criteria, $operator) {
                if (!empty($criteria)) {
                    foreach ($criteria as $coluna => $valor) {
                        if ($operator == false) {
                            $query->whereRaw($coluna.$valor);
                        } else {
                            $query->where($coluna, $operator, $valor);
                        }
                    }
                }
            });
            $sql = $grammar->compileDelete($builder);
        }

        // $values = array_values(array_filter($data, function ($data) {
        //     return ! $data instanceof Expression;
        // }));

        return array($table, $sql, $data, $criteria);
    }

    /**
     * Insere um registro
     * @param  string  $table Nome da tabela
     * @param  array   $data  Coluna e valor para inserir
     * @param  boolean $debug Ativa modo debug exibindo o sql
     * @return mixed          Retorna o id inserido ou a string sql
     */
    public function insert(
        string $table = '',
        array $data = array(),
        bool $debug = false
    ) {
        if (!$data) {
            return false;
        }

        return $debug ? $this->debug($table, $data, array(), 'insert') : DB::table($table)->insertGetId($data);
    }

    /**
     * Atualização de registro
     * @param  string  $table Nome da tabela
     * @param  int     $id    Id do registro
     * @param  array   $data  Dados para atualizar
     * @param  boolean $debug Exibe o sql modo debug
     * @return mixed          Retorna a quantidade registros afetados ou a string de debug
     */
    public function update(
        string $table = '',
        int $id = 0,
        array $data = array(),
        bool $debug = false
    ) {
        if (!$data || !$id) {
            return false;
        }

        return $debug ? $this->debug($table, $data, array(), 'update') : DB::table($table)->where('id', $id)->update($data);
    }

    /**
     * Atualiza o registro pela coluna informada
     * @param  string $table  Nome da tabela
     * @param  array  $column Coluna e valor para busca
     * @param  array  $data   Colunas e valores para atualização
     * @return mixed
     */
    public function updateByColumn(
        string $table = '',
        array $criteria = array(),
        array $data = array(),
        bool $debug = false
    ) {
        if (!$data || !$criteria) {
            return false;
        }

        $records = false;
        if ($debug) {
            $records = $this->debug($table, $data, $criteria, 'update');
        } else {
            $records = DB::table($table)
                         ->where(function ($query) use ($restricao) {
                            if (!empty($restricao)) {
                                foreach ($restricao as $coluna => $valor) {
                                    $query->where($coluna, '=', $valor);
                                }
                            }
                         })->update($data);
        }

        return $records;
    }

    /**
     * Atualizar registro pelo critério de busca informado
     * @param  string $table     Nome da tabela
     * @param  array  $restricao Critério de busca
     * @param  array  $data      Colunas e valores
     * @return mixed
     */
    public function updateByColumnWhere(
        string $table = '',
        array $restricao = array(),
        array $data = array(),
        bool $debug = false
    ) {
        if (!$data || !$restricao) {
            return false;
        }

        $records = false;
        if ($debug) {
            $records = $this->debug($table, $data, $restricao, 'update', false);
        } else {
            $records = DB::table($table)
                         ->where(function ($query) use ($restricao) {
                            if (!empty($restricao)) {
                                foreach ($restricao as $coluna => $valor) {
                                    $query->whereRaw($coluna." ".$valor);
                                }
                            }
                         })->update($data);
        }

        return $records;
    }

    /**
     * Remove um registro
     * @param  string  $table Nome da tabela
     * @param  int     $id    Id do registro
     * @param  bool    $debug Ativa o debug do sql
     * @return mixed           Retorna true se o registro foi removido
     */
    public function delete(
        string $table = '',
        int $id = 0,
        bool $debug = false
    ) {
        $records = false;
        if (!empty($table) && !empty($id)) {
            if ($debug) {
                $records = $this->debug($table, array(), array('id' => $id), 'delete');
            } else {
                $records = DB::table($table)->delete($id);
                $records = $records > 0 ? true : false;
            }
        }
        return $records;
    }

    /**
     * Remove registro pela coluna e valor informado
     * @param  string $table Nome da tabela
     * @param  array  $data  Colunas e valores
     * @return mixed         Retorna true se o registro foi removido
     */
    public function deleteByColumn(
        string $table = '',
        array $data = array(),
        bool $debug = false
    ) {
        $records = false;
        if (!empty($table) && !empty($data)) {
            if ($debug) {
                $records = $this->debug($table, array(), $data, 'delete');
            } else {
                $records = DB::table($table)
                             ->where(function ($query) use ($data) {
                                if (!empty($data)) {
                                    foreach ($data as $coluna => $valor) {
                                        $query->where($coluna, '=', $valor);
                                    }
                                }
                             })
                             ->delete();
                $records = $records > 0 ? true : false;
            }
        }
        return $records;
    }

    /**
     * Busca parametro
     * @param  string  $parametro  Nome do parametro
     * @param  int     $empresa_id Id da empresa
     * @return mixed               Retorna o valor do parametro ou null
     */
    public function buscaParametro(string $parametro = '', int $empresa_id = 0)
    {
        if (!$parametro) {
            return false;
        }

        $select = array('p.*');
        $record = DB::table('tparametros AS p')
                       ->where('p.parametro', '=', $parametro);

        if (!empty($empresa_id)) {
            $select = array('ep.valor');
            $record->join('tempresa_parametros AS ep', 'ep.parametro_id', '=', 'p.id')
                      ->where('empresa_id', '=', $empresa_id);
        }

        $r = $record->first($select);

        return !empty($r['valor']) ? $r['valor'] : null;
    }

    /**
     * Busca os emails não enviados
     * @return array  Retorna os emails
     */
    public function buscaEmailsNaoEnviados(): array
    {
        $emails = DB::table('tenvio_emails')
                    ->whereNull('enviado')
                    ->get();
        return $emails;
    }

    /**
     * Busca parametro por grupo
     * @param  string  $parametro  Nome do parametro
     * @param  int     $empresa_id Id da empresa
     * @return mixed               Retorna o valor do parametro ou null
     */
    public function buscaParametroGrupo(
        string $grupo = '',
        string $parametro = '',
        int $empr_id = 0
    ) {
        if (!$grupo || !$parametro) {
            return false;
        }

        $select = array('p.*');
        $record = DB::table('tparametros AS p')
                       ->where('p.parametro', '=', $parametro)
                       ->where('p.grupo', '=', $grupo);

        if (!empty($empresa_id)) {
            $select = array('ep.valor');
            $record->join('tempresa_parametros AS ep', 'ep.parametro_id', '=', 'p.id')
                      ->where('empresa_id', '=', $empresa_id);
        }

        $r = $record->first($select);

        return !empty($r['valor']) ? $r['valor'] : null;
    }

    /**
     * Recupera a sequencia pela descrição
     * @param  string      $sequencia Nome da sequencia
     * @param  int         $empr_id   Id da empresa
     * @return mixed                  Retorna o valor ou null
     */
    public function retornaSequencia(
        string $sequencia = '',
        int $empr_id = 0
    ) {
        if (!$empr_id) {
            return false;
        }

        $record = DB::table('tsequencias')
                    ->where('empresa_id', '=', $empr_id)
                    ->where('descricao', '=', $sequencia)
                    ->first(array('numero_seq'));

        return !empty($record['numero_seq']) ? $record['numero_seq'] : 0;
    }

    /**
     * Incrementa o valor da sequencia
     * @param  string $sequencia Nome da sequencia
     * @return int               Retorna os registros afetados
     */
    public function incrementaSequencia(string $sequencia = '')
    {
        return DB::table('tsequencias')->where('descricao', '=', $sequencia)->increment('numero_seq');
    }

      /**
     * Busca um modulo relacionado ao usuário
     * @param  array $restricao Critério de busca
     * @return array            Retorna os dados do modulo
     */
    public function buscaModuloUsuarioPerfis(array $restricao = array()): array
    {
        if (!$restricao) {
            return false;
        }

        $moduloUsuario = DB::table('tmodulos AS m')
                           ->select(
                               array(
                                    'm.*',
                                    'mp.permissao',
                                    'mp.empresa_empr_id'
                                )
                           )
                            ->join('tmodulos_perfil AS mp', 'mp.modulo_id', '=', 'm.id')
                            ->where(function ($query) use ($restricao) {
                                if (!empty($restricao)) {
                                    foreach ($restricao as $coluna => $valor) {
                                        $query->whereRaw($coluna.$valor);
                                    }
                                }
                            })
                            ->first();

        return !empty($moduloUsuario) ? $moduloUsuario : array();
    }

        /**
     * Busca os modulos relacionados ao usuário
     * @param  array $restricao Criterio de busca
     * @return array            Retorna os modulos relacionados ao usuário
     */
    public function buscaModulosPerfil(array $restricao = array()): array
    {
        $modulos = DB::table('tmodulos AS m')
                     ->select('m.*')
                     ->join('tmodulos_perfil AS mp', 'mp.modulo_id', '=', 'm.id')
                     ->where(function ($query) use ($restricao) {
                        if (!empty($restricao)) {
                            foreach ($restricao as $coluna => $valor) {
                                $query->whereRaw($coluna.$valor);
                            }
                        }
                     })
                     ->orderBy('m.ordem')
                     ->get();

        return !empty($modulos) ? $modulos : array();
    }
}
