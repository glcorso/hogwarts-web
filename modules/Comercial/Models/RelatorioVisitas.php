<?php

namespace Lidere\Modules\Comercial\Models;

use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use Yajra\Oci8\Eloquent\OracleEloquent;



/**
 * Model para retorno dos dados do banco
 *
 * @category   Controllers
 * @package    Modules
 * @subpackage RelatorioVisitas
 *
 * @author    Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright 2019 Lidere Sistemas
 * @license   GPL-3 https://www.lideresistemas.com.br/licence.txt
 * @link      https://www.lideresistemas.com.br/
 */
class RelatorioVisitas extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_comercial_rel_visitas';

    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'est_id'
        , 'usuario_id'
        , 'check_motivo_at'
        , 'obs_motivo_outros'
        , 'ra_sede'
        , 'ra_estrutura'
        , 'ra_estoque_geral'
        , 'check_climatizador'
        , 'check_clima_conc'
        , 'check_rodoar'
        , 'check_rodo_conc'
        , 'check_geladeira'
        , 'check_gela_conc'
        , 'folder_qtde'
        , 'banner_qtde'
        , 'catalogo_qtde'
        , 'obs_vendedor'
        , 'obs_pos_venda'
        , 'criado_em'
        , 'criado_por'
        , 'alterado_em'
        , 'alterado_por'
        , 'prospect_id'
        , 'check_motivo_trei'
        , 'check_motivo_com'
        , 'check_motivo_out'
        , 'contato_cliente'
        , 'telefone_cliente'
        , 'enviado'
        , 'data_visita'
    ];

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public static function criar($input = null)
    {
        $relatorioVisitas = null;

        try {
            
            $relatorioVisitas = new RelatorioVisitas();
            if(!empty($input['est_id'])){
                $relatorioVisitas->est_id  =  $input['est_id'];
            }
            
            $relatorioVisitas->usuario_id         = $_SESSION['usuario']['id']; 
            
            if(!empty($input['ra_sede'])){
                $relatorioVisitas->ra_sede            = $input['ra_sede'];
            }
            if(!empty($input['ra_estrutura'])){
                $relatorioVisitas->ra_estrutura       = $input['ra_estrutura'];
            }
            if(!empty($input['ra_estoque_geral'])){
                $relatorioVisitas->ra_estoque_geral   = $input['ra_estoque_geral'];
            }

            if(!empty($input['check_climatizador'])){
                $relatorioVisitas->check_climatizador = $input['check_climatizador'];
            }

            if(!empty($input['check_clima_conc'])){
                $relatorioVisitas->check_clima_conc   = $input['check_clima_conc'];
            }

            if(!empty($input['check_rodoar'])){
                $relatorioVisitas->check_rodoar       = $input['check_rodoar'];
            }

            if(!empty($input['check_rodo_conc'])){
                $relatorioVisitas->check_rodo_conc    = $input['check_rodo_conc'];
            }

            if(!empty($input['check_geladeira'])){
                $relatorioVisitas->check_geladeira    = $input['check_geladeira'];
            }

            if(!empty($input['check_gela_conc'])){
                $relatorioVisitas->check_gela_conc    = $input['check_gela_conc'];
            }
            
            if(!empty($input['obs_pos_venda'])){
                $relatorioVisitas->obs_pos_venda    = $input['obs_pos_venda'];
            }

            if(!empty($input['prospect_id'])){
                $relatorioVisitas->prospect_id      = $input['prospect_id'];
            }

            if(!empty($input['obs_motivo_outros'])){
                $relatorioVisitas->obs_motivo_outros  = $input['obs_motivo_outros'];
            }

            if(!empty($input['check_motivo_trei'])){
                $relatorioVisitas->check_motivo_trei  = $input['check_motivo_trei'];
            }

            if(!empty($input['check_motivo_com'])){
                $relatorioVisitas->check_motivo_com  = $input['check_motivo_com'];
            }

            if(!empty($input['check_motivo_out'])){
                $relatorioVisitas->check_motivo_out  = $input['check_motivo_out'];
            }

            if(!empty($input['check_motivo_at'])){
                $relatorioVisitas->check_motivo_at  = $input['check_motivo_at'];
            }

            if(!empty($input['check_motivo_eng'])){
                $relatorioVisitas->check_motivo_eng  = $input['check_motivo_eng'];
            }

            if(!empty($input['telefone_cliente'])){
                $relatorioVisitas->telefone_cliente   = $input['telefone_cliente'];
            }
            if(!empty($input['contato_cliente'])){
                $relatorioVisitas->contato_cliente    = $input['contato_cliente'];
            }
            if(!empty($input['folder_qtde'])){
                $relatorioVisitas->folder_qtde        = $input['folder_qtde'];
            }
            if(!empty($input['banner_qtde'])){
                $relatorioVisitas->banner_qtde        = $input['banner_qtde'];
            }
            if(!empty($input['catalogo_qtde'])){
                $relatorioVisitas->catalogo_qtde      = $input['catalogo_qtde'];
            }
            if(!empty($input['data_visita'])){
                $relatorioVisitas->data_visita        = $input['data_visita'];
            }
            if(!empty($input['enviado'])){
                $relatorioVisitas->enviado        = $input['enviado'];
            }
            $relatorioVisitas->tipo_rel           = $input['tipo_rel'];
            $relatorioVisitas->obs_vendedor       = $input['obs_vendedor'];
            $relatorioVisitas->criado_em          = date('d/m/Y H:i:s');
            $relatorioVisitas->criado_por         = $_SESSION['usuario']['id'];

            $relatorioVisitas->save();
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $relatorioVisitas;
    }
}
