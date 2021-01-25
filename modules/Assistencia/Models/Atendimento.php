<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Assistencia\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Classe para consulta, inclusão, edição e exclusão do ERP
 *
 * @author Sergio Sirtoli Jr.
 * @package Models
 * @category Model
 */
class Atendimento extends Model
{
    protected $connection;

    public function __construct()
    {
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    public function getClienteAssistencia($cpf_cnpj)
    {

        if(empty($cpf_cnpj)){
            return false;
        }

        $sql = " SELECT id, nome, telefone, e_mail, cep, endereco, nro numero, complemento, uf, cidade, bairro
                   FROM tsdi_assistencia_clientes
                  WHERE cpf_cnpj = '{$cpf_cnpj}' ";

        $cliente = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($cliente['0']) ? $cliente['0'] : false;

    }

    public function getClienteERP($cpf_cnpj)
    {

        if(empty($cpf_cnpj)){
            return false;
        }

        $sql = " SELECT te.id, te.descricao nome, te.cli_id, nvl(cont_ass.telefone, (SELECT '(' || tel.ddd || ')' || tel.telefone
             FROM ttel_est tel
            WHERE tel.ranking = 1
              AND rownum = 1
              AND tel.est_id = te.ID)) telefone , cont_ass.e_mail
                   FROM testabelecimentos te
              LEFT JOIN tsdi_assistencia_contatos cont_ass ON (te.ID = cont_ass.est_id)
                  WHERE ( te.cpf = '{$cpf_cnpj}' OR te.cnpj = '{$cpf_cnpj}')
                    AND ROWNUM = 1 ";

        $cliente = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($cliente['0']) ? $cliente['0'] : false;

    }


    public function cadastrarCliente($cliente)
    {

        if ( empty($cliente) ) {
            return false;
        }

        $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

        $sql = "
        BEGIN
            INSERT INTO tsdi_assistencia_clientes
            (id, nome, cpf_cnpj, telefone, e_mail, criado_por, criado_em)
            VALUES (seq_id_tsdi_assist_clientes.nextval, '{$cliente['nome']}', '{$cliente['cpf_cnpj']}', '{$cliente['telefone']}', '{$cliente['e_mail']}', '{$_SESSION['usuario']['id']}' ,SYSDATE);
        END;";

        $stmt = $pdo->prepare($sql);

        if ( !$stmt->execute() ) {
            return false;
        } else {
            $id = $pdo->lastInsertId('seq_id_tsdi_assist_clientes');
            return $id;
        }

    }

    public function getNumeroSerieOrSequencial($codigo,$tipo = 'serie')
    {

        if(empty($codigo)){
            return false;
        }

        $sql = " SELECT  v.nro_serie,
                         v.sequencial,
                         v.serie_id,
                         v.sequencial_id,
                         'E-'||v.item_id item_id,
                         v.cod_item,
                         v.desc_tecnica,
                         v.cod_cli,
                         v.descricao,
                         v.cpf_cnpj,
                         v.data_fab,
                         v.cli_id,
                         v.data_compra
                   FROM vsdi_assistencia_seq_serie v
                  WHERE 1 = 1 ";

        if($tipo == 'serie'){
            $sql .= " AND ( v.nro_serie = '{$codigo}')";
        }else {
            $sql .= " AND ( v.sequencial = '{$codigo}')";
        }

        $serie = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($serie['0']) ? $serie['0'] : false;

    }

    public function retornaItemDescricaoTecnica($string,$item_id, $testa_parametro = 'S')
    {
        $and_base = '';

        if($testa_parametro == 'S'){
            if(empty($item_id)){
                $itens_base = Core::parametro('assistencia_codigos_geladeira_padrao');
                if(!empty($itens_base)){
                    $itens_base = explode(',', $itens_base);
                    foreach ($itens_base as &$item) {
                        $item = "'".trim($item)."'";
                    }
                    $itens_base = implode(',', $itens_base);
                    $and_base = " AND ti.cod_item IN (".$itens_base.")";
                }
            }
        }

        $and = !empty($item_id) ? " AND ti.id = ".$item_id : '';
        $sql  = " SELECT id, codigo, descricao FROM (";
        $sql .= " SELECT 'E-'||ti.id id, ti.cod_item codigo, ti.desc_tecnica descricao
                   FROM titens ti
                  WHERE 1 = 1";
        if(!empty($string)){
            $sql .= " AND (upper(ti.cod_item) like upper('%{$string}%') OR upper(ti.desc_tecnica) like upper('%{$string}%') ) ";
        }
        $sql .= " {$and} {$and_base}";
        $sql .= " UNION ALL ";
        $sql .= " SELECT 'I-'||item_interno.id id, to_char(item_interno.id) codigo, item_interno.descricao
                    FROM tsdi_assistencia_itens item_interno 
                   WHERE 1 = 1 ";
        if(!empty($string)){
            $sql .= " AND (upper(item_interno.descricao) like upper('%{$string}%') ) ";
        }

        $sql .= ") ORDER BY 2 DESC ";


        $itens_desc = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        if(!empty($item_id)){
            return !empty($itens_desc['0']) ? $itens_desc['0'] : false;
        }else{
            return !empty($itens_desc) ? $itens_desc : false;
        }
    }     


    public function retornaClientes($string,$cliente_id)
    {

        $and = !empty($cliente_id) ? " AND te.id = ".$cliente_id : '';

        $sql = " SELECT te.id, tc.cod_cli codigo, (te.descricao || ' - Cidade: '||tcid.cidade|| ' - UF: '||uf.uf) descricao
                   FROM testabelecimentos te
             INNER JOIN tclientes tc ON (tc.id = te.cli_id)
              LEFT JOIN tcidades tcid ON (tcid.id = te.cid_id)
              LEFT JOIN tufs uf ON (uf.id = tcid.uf_id)
                  WHERE 1 = 1";
        if(!empty($string)){
            $sql .= " AND (upper(tc.cod_cli) like upper('%{$string}%') OR upper(te.descricao) like upper('%{$string}%') ) ";
        }
        $sql .= "{$and}";

        $clientes = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        if(!empty($cliente_id)){
            return !empty($clientes['0']) ? $clientes['0'] : false;
        }else{
            return !empty($clientes) ? $clientes : false;
        }
    }

    public function cadastrarRegistroAtendimento($registro)
    {

        try {
   
            if ( empty($registro) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "
            BEGIN
                INSERT 
                  INTO tsdi_assistencia_registro
                      (id, 
                       protocolo, 
                       cliente_assistencia_id, 
                       registro_id, 
                       responsavel_id, 
                       cliente_origem_id, 
                       serie_id, 
                       sequencial_id, 
                       item_id,
                       dt_fabricacao, 
                       dt_compra, 
                       motivo_id, 
                       defeito_principal_id, 
                       forn_defeito_id, 
                       obs_interna, 
                       obs_cliente, 
                       criado_por, 
                       criado_em, 
                       cliente_assistencia_erp_id,
                       item_interno_id,
                       nota_fiscal,
                       cliente_envio_id )
                 VALUES (seq_id_tsdi_assist_registro.nextval, 
                        {$registro['protocolo']}, 
                        {$registro['cliente_assistencia_id']}, 
                        {$registro['registro_id']}, 
                        {$registro['responsavel_id']},
                        {$registro['cliente_origem_id']}, 
                        {$registro['serie_id']}, 
                        {$registro['sequencial_id']}, 
                        {$registro['item_id']}, 
                        {$registro['dt_fabricacao']}, 
                        {$registro['dt_compra']},
                        {$registro['motivo_id']},
                        {$registro['defeito_principal_id']},
                        {$registro['forn_defeito_id']},
                        {$registro['obs_interna']},
                        {$registro['obs_cliente']},
                        {$_SESSION['usuario']['id']},
                        SYSDATE,
                        {$registro['cliente_assistencia_erp_id']},
                        {$registro['item_interno_id']},
                        {$registro['nota_fiscal']},
                        {$registro['cliente_envio_id']});
            END;";

            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else {
                $id = $pdo->lastInsertId('seq_id_tsdi_assist_registro');

                if(!empty($id)){

                    $status_novo_chamado = !empty($registro['status_id']) ? $registro['status_id']  :  Core::parametro('assistencia_status_novo_chamado');

                    $sql = " BEGIN
                              INSERT 
                                INTO tsdi_assistencia_historico
                                (id, 
                                 registro_id, 
                                 data, 
                                 responsavel_id, 
                                 status_id)
                                VALUES 
                                 (seq_id_tsdi_assist_historico.nextval,
                                  {$id},
                                  SYSDATE,
                                  {$registro['responsavel_id']},
                                  {$status_novo_chamado}); 
                            END;";

                    $stmt = $pdo->prepare($sql);

                    if ( !$stmt->execute() ) {
                        return false;
                    }
                }
                return $id;
            }

        } catch (Exception $e) {
            return false;
        }

    }

    public function getAssistenciaRegistro($type = 'result', $where = false, $limit = false)
    {

        $rows = self::select("v.*")
                    ->from('vsdi_assist_lista_chamados_2 v')
                    ->where(function ($query) use ($where) {
                        if (!empty($where)) {
                            foreach ($where as $column => $value) {
                                $query->whereRaw($column . " " . $value);
                            }
                        }
                    });

        if ($limit) {
            list($records, $offset) = $limit;
            $rows->skip($records)->take($offset);
        }

        $rows->orderBy('v.id','DESC');

       // var_dump($rows->toSql());die;

        if ( $type == 'result' ) {
          $rows = $rows->get();
        } else {
          $rows = $rows->first();
        }

        return !empty($rows) ? $rows->toArray() : null;

    }

    public function insereAtualizaTelefoneClienteErp($registro_cliente)
    {

        try {
   
            if ( empty($registro_cliente) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "BEGIN
                        INSERT 
                          INTO tsdi_assistencia_contatos
                              (id,
                               est_id,
                               telefone,
                               e_mail,
                               criado_por, 
                               criado_em)
                         VALUES (seq_id_tsdi_assist_contatos.nextval, 
                                {$registro_cliente['est_id']}, 
                                {$registro_cliente['telefone']}, 
                                {$registro_cliente['e_mail']}, 
                                {$_SESSION['usuario']['id']},
                                SYSDATE );
                    EXCEPTION
                      WHEN DUP_VAL_ON_INDEX THEN 
                         UPDATE tsdi_assistencia_contatos 
                            SET telefone = {$registro_cliente['telefone']},
                                e_mail = {$registro_cliente['e_mail']},
                                alterado_por = {$_SESSION['usuario']['id']},
                                alterado_em = SYSDATE
                          WHERE est_id = {$registro_cliente['est_id']};
                    END;";


            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else {
                $id = $pdo->lastInsertId('seq_id_tsdi_assist_contatos');
                return $id;
            }
        }catch(Exception $e){
            return false;
        }
    }

    public function insereAtualizaClienteAssistencia($registro_cliente)
    {

        try {
   
            if ( empty($registro_cliente) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "BEGIN
                         UPDATE tsdi_assistencia_clientes 
                            SET nome = {$registro_cliente['nome']},
                                telefone = {$registro_cliente['telefone']},
                                e_mail = {$registro_cliente['e_mail']},
                                alterado_por = {$_SESSION['usuario']['id']},
                                alterado_em = SYSDATE
                          WHERE id = {$registro_cliente['cliente_id']};
                    END;";


            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else {
                return true;
            }
        }catch(Exception $e){
            return false;
        }
    }

    public function insereArquivo($arquivo)
    {

        try {
   
            if ( empty($arquivo) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "BEGIN
                        INSERT 
                          INTO tsdi_assistencia_arquivos
                              (id,
                               registro_id,
                               criado_por, 
                               criado_em,
                               arquivo,
                               tipo)
                         VALUES (seq_id_tsdi_assist_arquivos.nextval, 
                                {$arquivo['registro_id']}, 
                                {$_SESSION['usuario']['id']},
                                SYSDATE,
                                {$arquivo['arquivo']},
                                {$arquivo['tipo']} );
                    END;";


            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else {
                $id = $pdo->lastInsertId('seq_id_tsdi_assist_arquivos');
                return $id;
            }
        }catch(Exception $e){
            return false;
        }
    }


    public function getAssistenciaArquivos($registro_id)
    {

        if(empty($registro_id)){
            return false;
        }

        $sql = " SELECT a.*
                   FROM tsdi_assistencia_arquivos a
                  WHERE a.registro_id = {$registro_id} ";

        $arquivos = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($arquivos) ? $arquivos : false;

    }

    public function getAssistenciaArquivosById($id)
    {

        if(empty($id)){
            return false;
        }

        $sql = " SELECT a.*
                   FROM tsdi_assistencia_arquivos a
                  WHERE a.id = {$id} ";

        $arquivo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($arquivo['0']) ? $arquivo['0'] : false;

    }

    public function atualizarRegistroAtendimento($registro)
    {

        try {
   
            if ( empty($registro) ) {
                return false;
            }

            //echo "<pre>";
            //var_dump($registro);die;    

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "
            BEGIN
                UPDATE tsdi_assistencia_registro
                   SET protocolo = {$registro['protocolo']}, 
                       cliente_assistencia_id = {$registro['cliente_assistencia_id']}, 
                       registro_id = {$registro['registro_id']}, 
                       cliente_origem_id = {$registro['cliente_origem_id']}, 
                       serie_id = {$registro['serie_id']} , 
                       sequencial_id = {$registro['sequencial_id']} , 
                       item_id = {$registro['item_id']},
                       dt_fabricacao = {$registro['dt_fabricacao']}, 
                       dt_compra = {$registro['dt_compra']} , 
                       motivo_id = {$registro['motivo_id']} ,
                       responsavel_id =  {$registro['responsavel_id']} ,
                       defeito_principal_id = {$registro['defeito_principal_id']}, 
                       obs_interna = {$registro['obs_interna']}, 
                       obs_cliente =  {$registro['obs_cliente']}, 
                       alterado_por = {$_SESSION['usuario']['id']}, 
                       alterado_em = SYSDATE, 
                       cliente_assistencia_erp_id = {$registro['cliente_assistencia_erp_id']},
                       item_interno_id = {$registro['item_interno_id']},
                       nota_fiscal = {$registro['nota_fiscal']},
                       cliente_envio_id = {$registro['cliente_envio_id']}
                WHERE id = {$registro['id']};
            END;";

            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else { 

                $status_novo_chamado = $registro['status_id'];

                $sql = " BEGIN
                          INSERT 
                            INTO tsdi_assistencia_historico
                            (id, 
                             registro_id, 
                             data, 
                             responsavel_id, 
                             status_id)
                            VALUES 
                             (seq_id_tsdi_assist_historico.nextval,
                              {$registro['id']},
                              SYSDATE,
                              {$registro['responsavel_id']},
                              {$status_novo_chamado}); 
                        END;";

                $stmt = $pdo->prepare($sql);

                if ( !$stmt->execute() ) {
                    return false;
                }

                return $registro['id'];
            }

        } catch (Exception $e) {
            return false;
        }
    }

    public function excluirArquivo($id)
    {

        try {
   
            if ( empty($id) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "BEGIN
                        DELETE 
                          FROM tsdi_assistencia_arquivos
                         WHERE id = {$id};
                    END;";


            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else {
                return true;
            }
        }catch(Exception $e){
            return false;
        }
    }

    public function retornaClienteAssistenciaSelect2($string = false , $cliente_id = false)
    {   

        $sql = " SELECT id, id codigo, nome descricao
                   FROM tsdi_assistencia_clientes
                  WHERE 1 = 1 ";

        if(!empty($string)){
            $sql .= " AND (upper(nome) like upper('%{$string}%')) ";
        }

        if(!empty($cliente_id)){
            $sql .= " AND id = {$cliente_id} ";
        }

        $clientes = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        if(!empty($cliente_id)){
            return !empty($clientes['0']) ? $clientes['0'] : false;
        }else{
            return !empty($clientes) ? $clientes : false;
        }

    }

    public function retornaItemErp($id)
    {

        if(empty($id)){
            return false;
        }

        $sql = " SELECT id, cod_item, desc_tecnica
                   FROM titens
                  WHERE id = '{$id}' ";

        $item = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($item['0']) ? $item['0'] : false;

    }

    public function retornaItemAss($id)
    {

        if(empty($id)){
            return false;
        }

        $sql = " SELECT id, id cod_item, descricao desc_tecnica
                   FROM tsdi_assistencia_itens 
                  WHERE id = '{$id}' ";

        $item = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($item['0']) ? $item['0'] : false;

    }

    public function retornaFornecedores($string,$fornecedor_id)
    {

        $and = !empty($fornecedor_id) ? " AND tf.id = ".$fornecedor_id : '';

        $sql = " SELECT tf.id, tf.cod_for codigo, tf.descricao
                   FROM tfornecedores tf
                  WHERE 1 = 1";
        if(!empty($string)){
            $sql .= " AND (upper(tf.cod_for) like upper('%{$string}%') OR upper(tf.descricao) like upper('%{$string}%') ) ";
        }
        $sql .= "{$and}";

        $fornecedores = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        if(!empty($fornecedor_id)){
            return !empty($fornecedores['0']) ? $fornecedores['0'] : false;
        }else{
            return !empty($fornecedores) ? $fornecedores : false;
        }
    }

    public function atualizarRegistroAtendimentoDetalhes($registro)
    {

        try {
   
            if ( empty($registro) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "
            BEGIN
                UPDATE tsdi_assistencia_registro
                   SET obs_interna_tecnica = {$registro['obs_interna_tecnica']}, 
                       forn_defeito_id     = {$registro['forn_defeito_id']}, 
                       clas_defeito       = {$registro['clas_defeito']}, 
                       responsabilidade    = {$registro['responsabilidade']}, 
                       defeito_tecnico_id  = {$registro['defeito_tecnico_id']}, 
                       alterado_por        = {$_SESSION['usuario']['id']}, 
                       responsavel_id      =  {$registro['responsavel_id']} ,
                       alterado_em         = SYSDATE ,
                       chamado_erp_id      = {$registro['chamado_erp_id']},
                       saida_produto       = {$registro['saida_produto']}
                WHERE id = {$registro['id']};
            END;";

            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else {

                $status_novo_chamado = $registro['status_id'];

                $sql = " BEGIN
                          INSERT 
                            INTO tsdi_assistencia_historico
                            (id, 
                             registro_id, 
                             data, 
                             responsavel_id, 
                             status_id)
                            VALUES 
                             (seq_id_tsdi_assist_historico.nextval,
                              {$registro['id']},
                              SYSDATE,
                              {$registro['responsavel_id']},
                              {$status_novo_chamado}); 
                        END;";

                $stmt = $pdo->prepare($sql);

                if ( !$stmt->execute() ) {
                    return false;
                }

                return $registro['id'];
            }

        } catch (Exception $e) {
            return false;
        }
    }


    public function adicionarAtendimentoProtocolo($atendimento)
    {

        try {
   
            if ( empty($atendimento) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "BEGIN
                        INSERT 
                          INTO tsdi_assistencia_atend
                              ( id, 
                                registro_id, 
                                dt_atend, 
                                responsavel_id, 
                                atendimento, 
                                criado_por, 
                                criado_em,
                                considerar_laudo)
                         VALUES (seq_id_tsdi_assist_atend.nextval, 
                                {$atendimento['registro_id']}, 
                                '{$atendimento['dt_atend']}',
                                {$_SESSION['usuario']['id']},
                                '{$atendimento['atendimento']}', 
                                {$_SESSION['usuario']['id']},
                                SYSDATE,
                                '{$atendimento['considerar_laudo']}');
                    END;";


            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else {
                $id = $pdo->lastInsertId('seq_id_tsdi_assist_atend');
                return $id;
            }
        }catch(Exception $e){
            return false;
        }
    }


    public function getAssistenciaAtendimentos($registro_id)
    {

        if(empty($registro_id)){
            return false;
        }

        $sql = " SELECT a.*
                   FROM tsdi_assistencia_atend a
                  WHERE a.registro_id = {$registro_id}  
                  ORDER by a.dt_atend DESC";

        $atendimentos = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($atendimentos) ? $atendimentos : false;

    }

     public function excluirAtendimentoTecnico($id)
    {

        try {
   
            if ( empty($id) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "
            BEGIN
                DELETE 
                  FROM tsdi_assistencia_atend
                 WHERE id = {$id};
            END;";

            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else {
                return $id;
            }

        } catch (Exception $e) {
            return false;
        }
    }

    public function editarAtendimentoProtocolo($atendimento)
    {

        try {
   
            if ( empty($atendimento) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "BEGIN
                        UPDATE tsdi_assistencia_atend
                            SET dt_atend         = '{$atendimento['dt_atend']}',   
                                atendimento      = '{$atendimento['atendimento']}', 
                                alterado_por     = {$_SESSION['usuario']['id']} , 
                                alterado_em      = SYSDATE,
                                considerar_laudo = '{$atendimento['considerar_laudo']}'
                          WHERE id = {$atendimento['id']} ;
                    END;";


            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else {
                $id =  $atendimento['id'];
                return $id;
            }
        }catch(Exception $e){
            return false;
        }
    }

    public function getAssistenciaStatus()
    {
        $sql = " SELECT s.*
                   FROM tsdi_assistencia_status s
                  WHERE s.situacao = 1 ";

        $status = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql); 

        return !empty($status) ? $status : false;

    }

    public function getChamadosErp($string = false ,$chamado_id = false)
    {
        $and = " ";
        if(!empty($chamado_id)){
            $and = " id = ".$chamado_id; 
        }

        $sql = " SELECT c.id, c.num_chamado codigo
                   FROM tchamados_ass c
                  WHERE 1 = 1 ";


        if(!empty($string)){
            $sql .= " AND (upper(c.num_chamado) like upper('%{$string}%')) ";
        }
        
        $sql.= $and;

        $chamados = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql); 

        return !empty($chamados) ? $chamados : false;

    }

    public function insereArquivoLaudo($arquivo)
    {

        try {
   
            if ( empty($arquivo) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "BEGIN
                        INSERT 
                          INTO tsdi_assistencia_arquivo_laudo
                              (id,
                               registro_id,
                               criado_por, 
                               criado_em,
                               arquivo,
                               tipo)
                         VALUES (seq_id_tsdi_assist_arquivo_ld.nextval, 
                                {$arquivo['registro_id']}, 
                                {$_SESSION['usuario']['id']},
                                SYSDATE,
                                {$arquivo['arquivo']},
                                {$arquivo['tipo']} );
                    END;";


            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else {
                $id = $pdo->lastInsertId('seq_id_tsdi_assist_arquivo_ld');
                return $id;
            }
        }catch(Exception $e){
            return false;
        }
    }

    public function getAssistenciaArquivoLaudo($registro_id)
    {

        if(empty($registro_id)){
            return false;
        }

        $sql = " SELECT a.*
                   FROM tsdi_assistencia_arquivo_laudo a
                  WHERE a.registro_id = {$registro_id} ";

        $arquivo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($arquivo) ? $arquivo : false;

    }

    public function excluirArquivoLaudo($id)
    {

        try {
   
            if ( empty($id) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "BEGIN
                        DELETE 
                          FROM tsdi_assistencia_arquivo_laudo
                         WHERE id = {$id};
                    END;";


            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            } else {
                return true;
            }
        }catch(Exception $e){
            return false;
        }
    }

    public function getAssistenciaArquivosLaudoById($id)
    {

        if(empty($id)){
            return false;
        }

        $sql = " SELECT a.*
                   FROM tsdi_assistencia_arquivo_laudo a
                  WHERE a.id = {$id} ";

        $arquivo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($arquivo['0']) ? $arquivo['0'] : false;

    }

     public function getClienteERPId($id)
    {

        if(empty($id)){
            return false;
        }

        $sql = " SELECT te.id, te.descricao nome, te.cli_id, REPLACE (REPLACE (REPLACE( DECODE (te.tp_pes, 'J', FSDI_FORM_CNPJ_CPF (te.cnpj,'J') , FSDI_FORM_CNPJ_CPF (te.cpf,'F') )  ,'.', ''),'/','' ),'-', '') cpf_cnpj, NVL(cont_ass.telefone, '('||tel.DDD||')'||tel.telefone) telefone , NVL(cont_ass.e_mail,tmail.e_mail) e_mail,DECODE (te.tp_pes, 'J', FSDI_FORM_CNPJ_CPF (te.cnpj,'J') , FSDI_FORM_CNPJ_CPF (te.cpf,'F')) cpf_cnpj_format, te.endereco, te.cep, cid.cidade, uf.uf, te.bairro, te.nr_endereco nro
                   FROM testabelecimentos te 
                LEFT JOIN tsdi_assistencia_contatos cont_ass ON (te.ID = cont_ass.est_id)
                LEFT JOIN ttel_est tel ON (tel.est_id = te.id and tel.ranking = 1)
                LEFT JOIN temail_est tmail ON (tmail.est_id = te.id and tmail.ranking = 1)
                LEFT JOIN tcidades cid ON (cid.id = te.cid_id)
                LEFT JOIN tufs uf ON (uf.id = cid.uf_id)
                  WHERE te.id = {$id}
                    AND ROWNUM = 1 ";

        $cliente = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($cliente['0']) ? $cliente['0'] : false;

    }

    public function retornaClienteConsultaSelect2($string = false , $cliente_id = false)
    {   

        $sql = " SELECT 'I-'||id id, id codigo, nome descricao
                   FROM tsdi_assistencia_clientes
                  WHERE 1 = 1 ";

        if(!empty($string)){
            $sql .= " AND (upper(nome) like upper('%{$string}%')) ";
        }

        if(!empty($cliente_id)){
            $sql .= " AND id = {$cliente_id} ";
        }

        $sql .= " UNION ALL ";

        $sql .= " SELECT 'E-'||te.id id, tc.cod_cli codigo, te.descricao
                   FROM testabelecimentos te
             INNER JOIN tclientes tc ON (tc.id = te.cli_id)
                  WHERE 1 = 1";
        if(!empty($string)){
            $sql .= " AND (upper(tc.cod_cli) like upper('%{$string}%') OR upper(te.descricao) like upper('%{$string}%') ) ";
        }

        $clientes = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        if(!empty($cliente_id)){
            return !empty($clientes['0']) ? $clientes['0'] : false;
        }else{
            return !empty($clientes) ? $clientes : false;
        }

    }

    public function atualizaRecebimentoMaterial($registro_id)
    {

        try {
   
            if ( empty($registro_id) ) {
                return false;
            }

            $pdo = DB::connection('oracle_'.$_SESSION['empresa']['id'])->getPdo();

            $sql = "
            BEGIN
                UPDATE tsdi_assistencia_registro
                   SET material_recebido = '1',
                       recebido_por = {$_SESSION['usuario']['id']},
                       recebido_em = SYSDATE
                WHERE id = {$registro_id};
            END;";

            $stmt = $pdo->prepare($sql);

            if ( !$stmt->execute() ) {
                return false;
            }

            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }

    public function retornaClientesCnpj($string,$cliente_id)
    {
        $and = !empty($cliente_id) ? " AND te.id = ".$cliente_id : '';

        $string = str_replace('/', '', $string);
        $string = str_replace('.', '', $string);
        $string = str_replace('-', '', $string);

        $sql = " SELECT te.id, tc.cod_cli codigo, (te.descricao || ' -  Cnpj/Cpf: '|| DECODE (te.tp_pes,
           'J', fsdi_form_cnpj_cpf (te.cnpj, 'J'),
           fsdi_form_cnpj_cpf (te.cpf, 'F')
          ) ||' - Cidade: '||tcid.cidade|| ' - UF: '||uf.uf) descricao
                   FROM testabelecimentos te
             INNER JOIN tclientes tc ON (tc.id = te.cli_id)
              LEFT JOIN tcidades tcid ON (tcid.id = te.cid_id)
              LEFT JOIN tufs uf ON (uf.id = tcid.uf_id)
                  WHERE 1 = 1";

        if(!empty($string)){
            $sql .= " AND (upper(tc.cod_cli) like upper('%{$string}%') OR upper(te.descricao) like upper('%{$string}%') ";


            if((int)$string != 0) {
                $sql .=   " OR upper(te.cnpj) like '%".ltrim((int)($string))."%' OR upper(te.cpf) like '%".ltrim((int)($string))."%' ) " ;
            }else{
                $sql .= " )";
            }
        }

        $sql .= "{$and}";


        //echo "<pre>";
        //var_export($sql);die;
        $clientes = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        if(!empty($cliente_id)){
            return !empty($clientes['0']) ? $clientes['0'] : false;
        }else{
            return !empty($clientes) ? $clientes : false;
        }
    }


    public function getRelatorioDefeitos($filtros)
    {

        $and = " ";
        if(!empty($filtros['recebido_em'])){
            $and .= " AND TRUNC(recebido_em) ".$filtros['recebido_em']; 
        }

        if(!empty($filtros['item_id'])){
            $and .= " AND item_id ".$filtros['item_id']; 
        }

        if(!empty($filtros['defeito_tecnico_id'])){
            $and .= " AND defeito_tecnico_id ".$filtros['defeito_tecnico_id']; 
        }
        

        /*$sql = "  SELECT nvl(defeito_tecnico_id,-1) defeito_tecnico_id, cod_defeito_tecnico, desc_defeito_tecnico, item_id, cod_item, desc_tecnica, count(*) quantidade
                  FROM vsdi_assist_relatorio_defeitos
                 WHERE status_id = 3 
                   {$and}
              GROUP BY defeito_tecnico_id,cod_defeito_tecnico, desc_defeito_tecnico, item_id,  cod_item, desc_tecnica
              ORDER BY 7 desc";*/


        $sql = " SELECT nvl(defeito_tecnico_id,-1) defeito_tecnico_id, cod_defeito_tecnico, desc_defeito_tecnico, item_agrup item_id, item_agrup cod_item, count(*) quantidade
                  FROM vsdi_assist_relt_def_agrup
                 WHERE status_id = 3
                  {$and}
                GROUP BY defeito_tecnico_id,cod_defeito_tecnico, desc_defeito_tecnico, item_agrup,  item_agrup
                ORDER BY 6 DESC";

        $itens = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($itens) ? $itens : false;

    }

    public function getRelatorioListagem($filtros)
    {

        $and = " ";

        if(!empty($filtros['recebido_em'])){
            $and .= " AND TRUNC(recebido_em) ".$filtros['recebido_em']; 
        }

      /*  if(!empty($filtros['item_id'])){
            $and .= " AND item_id ".$filtros['item_id']; 
        }*/

        if(!empty($filtros['defeito_tecnico_id'])){
            $and .= " AND defeito_tecnico_id ".$filtros['defeito_tecnico_id']; 
        }  

        if(!empty($filtros['cliente_origem_id'])){
            $and .= " AND cliente_origem_id ".$filtros['cliente_origem_id']; 
        }  

        if(!empty($filtros['cliente_assistencia_id'])){
            $and .= " AND cliente_assistencia_id ".$filtros['cliente_assistencia_id']; 
        } 

        if(!empty($filtros['cliente_assistencia_erp_id'])){
            $and .= " AND cliente_assistencia_erp_id ".$filtros['cliente_assistencia_erp_id']; 
        } 

        if(!empty($filtros['saida_produto'])){
            $and .= " AND saida_produto_sig ".$filtros['saida_produto']; 
        } 

        if(!empty($filtros['material_recebido'])){
            $and .= " AND material_recebido ".$filtros['material_recebido']; 
        }


        $sql = "SELECT item_agrup item_id, item_agrup cod_item, count(id) total
                  FROM vsdi_assist_relt_def_agrup 
                 WHERE 1 = 1   
                   {$and}
              GROUP BY item_agrup
            ORDER BY 3 DESC";

        $itens = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        return !empty($itens) ? $itens : false;

    }

   
    public function retornaClientesCnpjAPI($string,$cliente_id)
    {
        $and = !empty($cliente_id) ? " AND te.id = ".$cliente_id : '';

        $string = str_replace('/', '', $string);
        $string = str_replace('.', '', $string);
        $string = str_replace('-', '', $string);

        $sql = " SELECT te.id, tc.cod_cli codigo, (te.descricao || ' -  Cnpj/Cpf: '|| DECODE (te.tp_pes,
           'J', fsdi_form_cnpj_cpf (te.cnpj, 'J'),
           fsdi_form_cnpj_cpf (te.cpf, 'F')
          ) ||' - Cidade: '||tcid.cidade|| ' - UF: '||uf.uf) descricao , (SELECT '(' || tel.ddd || ')' || tel.telefone
             FROM ttel_est tel
            WHERE tel.ranking = 1
              AND rownum = 1
              AND tel.est_id = te.ID) telefone, uf.uf, tcid.cidade
                   FROM testabelecimentos te
             INNER JOIN tclientes tc ON (tc.id = te.cli_id)
              LEFT JOIN tcidades tcid ON (tcid.id = te.cid_id)
              LEFT JOIN tufs uf ON (uf.id = tcid.uf_id)
                  WHERE 1 = 1";

        if(!empty($string)){
            $sql .= " AND (upper(tc.cod_cli) like upper('%{$string}%') OR upper(te.descricao) like upper('%{$string}%') ";


            if((int)$string != 0) {
                $sql .=   " OR upper(te.cnpj) like '%".ltrim((int)($string))."%' OR upper(te.cpf) like '%".ltrim((int)($string))."%' ) " ;
            }else{
                $sql .= " )";
            }
        }

        $sql .= "{$and}";


        //echo "<pre>";
        //var_export($sql);die;
        $clientes = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        if(!empty($cliente_id)){
            return !empty($clientes['0']) ? $clientes['0'] : false;
        }else{
            return !empty($clientes) ? $clientes : false;
        }
    }


    public function retornaClientesUsuariosSelect2($string = false , $cliente_id = false)
    {   

        $sql = " SELECT te.id||'#'||tc.cod_cli||'#'||te.descricao id, tc.cod_cli codigo, te.descricao
                   FROM testabelecimentos te
             INNER JOIN tclientes tc ON (tc.id = te.cli_id)
                  WHERE 1 = 1";
        if(!empty($string)){
            $sql .= " AND (upper(tc.cod_cli) like upper('%{$string}%') OR upper(te.descricao) like upper('%{$string}%') ) ";
        }

        $clientes = DB::connection('oracle_'.$_SESSION['empresa']['id'])->select($sql);

        if(!empty($cliente_id)){
            return !empty($clientes['0']) ? $clientes['0'] : false;
        }else{
            return !empty($clientes) ? $clientes : false;
        }

    }


    public function getAgrupadorListaItens($type = 'result', $where = false, $limit = false)
    {

        $rows = self::select("v.*")
                    ->from('vsdi_lista_agrp_geladeiras v')
                    ->where(function ($query) use ($where) {
                        if (!empty($where)) {
                            foreach ($where as $column => $value) {
                                $query->whereRaw($column . " " . $value);
                            }
                        }
                    });

        if ($limit) {
            list($records, $offset) = $limit;
            $rows->skip($records)->take($offset);
        }

        $rows->orderBy('v.obser','ASC');

       // var_dump($rows->toSql());die;

        if ( $type == 'result' ) {
          $rows = $rows->get();
        } else {
          $rows = $rows->first();
        }

        return !empty($rows) ? $rows->toArray() : null;

    }


}
