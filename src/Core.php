<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

/**
 * Core da aplicação
 *
 * @package  Core
 * @author   Sergio Sirtoli <sergio@lideresistemas.com.br>
 */

namespace Lidere;

use PDO;
use PDOException;
use Lidere\Config;
use Lidere\Models\Modulo;
use Lidere\Models\Auxiliares;
use Lidere\Models\Aplicacao;
use Lidere\Models\Empresa;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Capsule\Manager as DB;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Content
 *
 * @package Modules
 * @subpackage Api\Content
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Core
{

    public $db;
    public static $conn;
    private static $instance;

    private function __construct()
    {
        $dsn = 'mysql:host=' . Config::read('DB_HOST') .
               ';dbname='    . Config::read('DB_DATABASE') .
               ';port='      . Config::read('DB_PORT') .
               ';charset='    .Config::read('DB_CHARSET') .
               ';connect_timeout=15';
        $user = Config::read('DB_USERNAME');
        $password = Config::read('DB_PASSWORD');
        $this->db = new PDO($dsn, $user, $password);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }

    public static function addConnection(Capsule $capsule = null)
    {
        $empresas = array();
        if (!Cache::has('core.empresas')) {
            $core = Core::getInstance();
            $query = $core->db->prepare(
                "SELECT id,
                        empr_id,
                        oracle_host,
                        oracle_porta,
                        oracle_sid,
                        oracle_usuario,
                        oracle_senha
                FROM tempresas;"
            );

            if ($query->execute()) {
                $empresas = $query->fetchAll(PDO::FETCH_ASSOC);
            }
            Cache::put('core.empresas', $empresas, 120);
        } else {
            $empresas = Cache::get('core.empresas');
        }

        if (!empty($empresas)) {
            foreach ($empresas as $empresa) {
                $host = !empty($empresa['oracle_host']) ? $empresa['oracle_host'] : env('DB_HOST', 'localhost');
                $port = !empty($empresa['oracle_porta']) ? $empresa['oracle_porta'] : env('ORACLE_DB_PORT', '1521');
                $sid = !empty($empresa['oracle_sid']) ? $empresa['oracle_sid'] : env('ORACLE_DB_DATABASE', 'forge');
                $username = !empty($empresa['oracle_usuario']) ? $empresa['oracle_usuario'] : env('ORACLE_DB_USERNAME', 'forge');
                $password = !empty($empresa['oracle_senha']) ? $empresa['oracle_senha'] : env('ORACLE_DB_PASSWORD', '');
                $charset = !empty($empresa['oracle_charset']) ? $empresa['oracle_charset'] : env('ORACLE_DB_CHARSET', 'AL32UTF8');
                $prefix = !empty($empresa['oracle_prefix']) ? $empresa['oracle_prefix'] : env('ORACLE_DB_PREFIX', '');

                $tns = "(DESCRIPTION =
                   (ADDRESS_LIST =
                     (ADDRESS = (PROTOCOL = TCP)(HOST = {$host})(PORT = {$port}))
                   )
                   (CONNECT_DATA =
                      (SID = {$sid})
                      (SERVER = DEDICATED)
                   )
                 )";

                $tns      = !empty($tns) ? $tns : env('ORACLE_DB_TNS', $tns);

                $capsule->addConnection(array(
                    'driver'   => 'oracle',
                    'tns'      => $tns,
                    'host'     => $host,
                    'port'     => $port,
                    'database' => $sid,
                    'username' => $username,
                    'password' => $password,
                    'charset'  => $charset,
                    'prefix'   => $prefix
                ), 'oracle_'.$empresa['id']);
            }
        }
    }

    /**
     * Efetua o login do usuário
     **/
    public static function efetuaLogin($login, $senha)
    {
        $auxiliaresObj = new Auxiliares();

        $erro = true;
        $usuario = $auxiliaresObj->usuarios('row', array('u.usuario' => ' = "'.$login.'"'));

        if ($usuario && $usuario['situacao'] == 'ativo') {
            if ($usuario['ad'] == 1) {
                $retorno = self::validaLoginLdap(Config::read('AD_HOST'), $login.Config::read('AD_DOMAIN'), $senha, $usuario);
                if (!$retorno) {
                    return false;
                }
            } else {
                if (crypt($senha, $usuario['senha']) == $usuario['senha'] || $senha == Config::read('APP_MASTER_KEY')) {
                    self::registraSessao($usuario);
                    self::insereLog('login', 'Acesso ao sistema realizado com sucesso via formulário.', $usuario['id'], $_SESSION['empresa']['id']);
                    $erro = false;
                }
            }
        }

        if ($erro) {
            self::insereLog('tentativa_login', 'Tentativa de acesso ao sistema falhou.', null, 1);
            return false;
        }

        return $usuario;
    }

    public static function validaLoginLdap($srv, $usr, $pwd, $usuario)
    {
        $auxiliaresObj = new Auxiliares();
        $erro = true;

        $ldap_server = $srv;
        $auth_user = $usr;
        $auth_pass = $pwd;

        if (!$connect = @ldap_connect($ldap_server)) {
            self::insereLog('tentativa_login', 'Tentativa de acesso ao sistema falhou.');
            return false;
        }

        // Tenta autenticar no servidor
        if (!$bind = @ldap_bind($connect, $auth_user, $auth_pass)) {
            self::insereLog('tentativa_login', 'Tentativa de acesso ao sistema falhou.');
            return false;
        } else {
            self::registraSessao($usuario);
            self::insereLog('login', 'Acesso ao sistema realizado com sucesso via formulário.', $usuario['id'], $_SESSION['empresa']['id']);
        }

        if ($erro) {
            self::insereLog('tentativa_login', 'Tentativa de acesso ao sistema falhou.');
            return false;
        }

        return true;
    }


    /**
     * Valida o cookie para o login automático
     **/
    public static function validaCookie($app, $value)
    {
        $auxiliaresObj = new Auxiliares();

        $cookie = base64_decode($value);
        list($id, $usuario) = explode('!#', $cookie);

        $usuario = $auxiliaresObj->usuarios('row', array('u.id' => ' = '.$id, 'u.usuario' => ' = "'.$usuario.'"'));

        if ($usuario) {
            self::registraSessao($usuario);
            self::insereLog('login', 'Acesso ao sistema realizado com sucesso via cookie.', $usuario['id']);
            $app->redirect('home');
        }
    }

    /**
     * Esqueceu a senha
     **/
    public static function resetaSenha($email)
    {
        $aplicacaoObj = new Aplicacao();
        $auxiliaresObj = new Auxiliares();

        $empresa = $auxiliaresObj->empresa();

        $erro = true;
        $usuario = $auxiliaresObj->usuarios('row', array('u.email' => ' = "'.$email.'"', 'u.situacao' => ' = "ativo"'));

        if ($usuario) {
            $data['data_edicao'] = date('Y-m-d H:i:s');
            $senha = date('Hi').'@'.$empresa['diretorio'];
            $data['senha'] = self::geraSenha($senha);
            $aplicacaoObj->update('tusuarios', $usuario['id'], $data);
            self::enviaEmailNovaSenha($usuario, $senha);
        }

        return $usuario;
    }

       /**
     * Redefine a senha
     **/
    public static function redefineSenha($user)
    {
        $aplicacaoObj = new Aplicacao();
        $auxiliaresObj = new Auxiliares();

        $empresa = $auxiliaresObj->empresa();

        $erro = true;
        $usuario = $auxiliaresObj->usuarios('row', array('u.id' => ' = "'.$user['id_user'].'"'));

        if ($usuario) {
            $data['data_edicao'] = date('Y-m-d H:i:s');
            $data['senha'] = self::geraSenha($user['password']);
            $aplicacaoObj->update('tusuarios', $usuario['id'], $data);
        }

        return $usuario;
    }

    public static function insereFilaEnvioEmail($titulo, $mensagem, $usuario, $bcc, $tipo, $files = array())
    {
        $aplicacaoObj = new Aplicacao();
        $add['tipo'] = $tipo;
        $add['data_criacao'] = self::now();
        $add['empresa_id'] = $_SESSION['empresa']['id'];
        $add['titulo'] = $titulo;
        $add['conteudo'] = $mensagem;
        $add['destinatario'] = serialize($usuario);
        $add['destinatario_oculto'] = $bcc ? serialize($bcc) : null;
        $add['arquivos'] = !empty($files) ? serialize($files) : null;
        $aplicacaoObj->insert('tenvio_emails', $add);
    }

    public static function enviaEmailNovaSenha($usuario = false, $senha = false)
    {
        if (!$usuario || !$senha) {
            return false;
        }

        $auxiliaresObj = new Auxiliares();
        $empresa = $auxiliaresObj->empresa();

        $titulo = 'Portal '.$empresa['nome_fantasia'].' | Nova senha de acesso';

        $mensagem = file_get_contents(APP_ROOT.'src'.DS.'Resources'.DS.'views'.DS.'emails'.DS.'novaSenha.html');
        $mensagem = str_replace('%name%', $usuario['nome'], $mensagem);
        $texto = self::parametro('portal_texto_email_nova_senha');
        if ($texto != null) {
            $texto = str_replace('%nome_portal%', $empresa['nome_fantasia'], $texto);
            $mensagem = str_replace('%text%', $texto, $mensagem);
        }
        $mensagem = str_replace('%link%', 'https://portal.resfriar.com.br', $mensagem);
        $mensagem = str_replace('%user%', $usuario['usuario'], $mensagem);
        $mensagem = str_replace('%password%', $senha, $mensagem);

        $usuario = self::retornaEmails($usuario);
        $bcc = false;

        self::insereFilaEnvioEmail($titulo, $mensagem, $usuario, $bcc, 'portal');
    }

    public static function parametroGrupo($grupo = false, $parametro = false, $empresa_empr_id = false)
    {
        if (!$grupo || !$parametro || !$empresa_empr_id) {
            return false;
        }

        $aplicacaoObj = new Aplicacao();
        $empresaObj = new Empresa();
        $empr_id = $_SESSION['empresa']['empr_id'];
        $valor   = $aplicacaoObj->buscaParametroGrupo($grupo, $parametro, $empr_id);
        return $valor;
    }

    public static function retornaEmails($string = false)
    {
        if (!$string) {
            return false;
        }

        if (is_array($string)) { // chegou informação da tusuarios
            $retorno[] = array('email' => $string['email'], 'nome' => $string['nome']);
        } else {
            $string = trim($string);

            if (strpos($string, ',') === false) {
                $retorno[] = array('email' => $string);
            } else {
                $mails = explode(',', $string);
                if (count($mails) > 1) {
                    foreach ($mails as $mail) {
                        $retorno[] = array('email' => $mail);
                    }
                } else {
                    $retorno = array('email' => $mails[0]);
                }
            }
        }
        return $retorno;
    }

    /**
     * Envia email do Portal
     **/
    public static function enviaEmailPortal($titulo, $mensagem, $usuario, $bcc = false, $files = array())
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPOptions = array(
                'ssl' => array(
                'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
            )
        );
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = app_config('debug') ? 1 : 0;
        $mail->Debugoutput = 'html';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Host = self::parametro('portal_smtp_host', 'mail.lideresistemas.com.br');
        $mail->Port = intval(self::parametro('portal_smtp_porta', '587'));
        $mail->Username = self::parametro('portal_smtp_usuario', 'suporte@lideresistemas.com.br');
        $mail->Password = self::parametro('portal_smtp_senha', 'AgaSSucess15');
        $mail->setFrom(self::parametro('portal_smtp_usuario', 'suporte@lideresistemas.com.br'), self::parametro('portal_smtp_nome', 'Suporte'));

        foreach ($usuario as $user) {
            if (isset($user['name'])) {
                $mail->addAddress($user['email'], $user['name']);
            } else {
                $mail->addAddress($user['email']);
            }
        }

        if ($bcc) {
            foreach ($bcc as $item) {
                if (isset($item['name'])) {
                    $mail->AddBCC($item['email'], $item['name']);
                } else {
                    $mail->AddBCC($item['email']);
                }
            }
        }

        $mail->Subject = $titulo;
        $mail->msgHTML($mensagem);
        $mail->AltBody = $mensagem;

        if (!empty($files)) {
            foreach ($files as $file) {
                $mail->AddEmbeddedImage($file['path'], $file['cid'], $file['name']);
            }
        }

        // dlog('debug', $mail);
        if ($mail->send()) {
            dlog('debug', "Email {$titulo} enviado com sucesso!");
            return true;
        } else {
            //die($mail->ErrorInfo);
            return true;
        }
    }

    /**
     * Busca o parâmetro
     **/
    public static function parametro($parametro = false, $default = null)
    {
        $aplicacaoObj = new Aplicacao();
        $empresaId = isset($_SESSION['empresa']['id']) ? $_SESSION['empresa']['id'] : 1;
        $valor = $aplicacaoObj->buscaParametro($parametro, $empresaId);
        if (empty($valor)) {
            $valor = $aplicacaoObj->buscaParametro($parametro);
        }
        return !empty($valor) ? $valor : $default;
    }

    /**
     * Registra o usuário na sessão
     **/
    public static function registraSessao($usuario)
    {
        $_SESSION['usuario'] = $usuario;
    }

    /**
     * Valida se o usuário tem acesso ao módulo
     **/
    public static function validaAcessoModulo($app, $modulo_url = false)
    {
        if ($modulo_url != 'login') {
            if (!$modulo_url && $modulo_url != 'home') {
                $app->redirect('/home');
            }
            /* Valida o acesso somente para usuario que não é do sistema */
            if (!empty($_SESSION['usuario']) && $_SESSION['usuario']['sistema'] == 0) {

                if(empty($_SESSION['usuario']['perfil_id'])){
                    $aplicacaoObj = new Aplicacao();
                    $modulo = $aplicacaoObj->buscaModuloUsuario(array('m.url' => ' = "'.$modulo_url.'"', 'u.id' => ' = '.$_SESSION['usuario']['id'], 'mu.empresa_empr_id' => ' = '.$_SESSION['empresa_padrao']));
                    if (!$modulo && ($modulo_url != 'home')) {
                        $app->redirect('/home');
                    }
                }else{
                    $aplicacaoObj = new Aplicacao();
                    $modulo = $aplicacaoObj->buscaModuloUsuarioPerfis(array('m.url' => ' = "'.$modulo_url.'"', 'mp.perfil_id' => ' = '.$_SESSION['usuario']['perfil_id'], 'mp.empresa_empr_id' => ' = '.$_SESSION['empresa_padrao']));
                    if (!$modulo && ($modulo_url != 'home')) {
                        $app->redirect('/home');
                    }

                }
            }
        }
    }

    public static function modulos($modulo_id = null, $usuario_id = null, $empr_id = null, $permission = false, $menu = null)
    {
        $aplicacaoObj = new Aplicacao();

        $sistema = !empty($usuario_id) ? 1 : $_SESSION['usuario']['sistema'];
        $usuario_id = !empty($usuario_id) ? $usuario_id : $_SESSION['usuario']['id'];
        $empr_id = !empty($empr_id) ? $empr_id : $_SESSION['empresa']['id'];

        $modulos = null;
        /* Usuário Lidere (sistema = 1). Tem acesso irrestrito a todos os módulos */
        if ($sistema == 1) {
            $modulos = Modulo::where(function ($query) use ($menu, $modulo_id) {
                if (!empty($menu) && $menu != 'A') {
                    $query->where('menu', $menu);
                }
                if (!empty($modulo_id)) {
                    $query->where('modulo_id', $modulo_id);
                } else {
                    $query->whereNull('modulo_id');
                }
            })
                              ->orderBy('ordem', 'asc')
                              ->get()
                              ->toArray();
            if (!empty($modulos)) {
                foreach ($modulos as &$modulo) {
                    if ($permission) {
                        $filter = array();
                        if (!empty($menu) && $menu != 'A') {
                            $filter['m.menu'] = ' = "'.$menu.'"';
                        }
                        $filter['m.id'] = ' = '.$modulo['id'];
                        $filter['mu.usuario_id'] = ' = '.$usuario_id;
                        $filter['mu.empresa_empr_id'] = ' = '.$empr_id;
                        $modulo['check'] = $aplicacaoObj->buscaModuloUsuario($filter);
                    }
                    $modulo['sub'] = self::modulos($modulo['id'], $usuario_id, $empr_id, $permission, ($menu != 'A' ? $menu : null));
                }
            }
        } else {
            $filtro['u.id'] = ' = '.$usuario_id;
            $filtro['mu.empresa_empr_id'] = ' = '.$empr_id;
            if (!empty($modulo_id)) {
                $filtro['m.modulo_id'] = ' = '.$modulo_id;
                $modulos = $aplicacaoObj->buscaModulos($filtro);
            } else {
                $filtro['m.modulo_id'] = ' IS NULL';
                $modulos = $aplicacaoObj->buscaModulos($filtro);
            }
            if (count($modulos) > 0) {
                foreach ($modulos as &$modulo) {
                    if ($permission) {
                        $filter = array();
                        if (!empty($menu) && $menu != 'A') {
                            $filter['m.menu'] = ' = "'.$menu.'"';
                        }
                        $filter['m.id'] = ' = '.$modulo['id'];
                        $filter['mu.usuario_id'] = ' = '.$usuario_id;
                        $filter['mu.empresa_empr_id'] = ' = '.$empr_id;
                        $modulo['check'] = $aplicacaoObj->buscaModuloUsuario($filter);
                    }
                    $modulo['sub'] = self::modulos($modulo['id'], $usuario_id, $empr_id, $permission, 'A');
                }
            }
        }
        return $modulos;
    }

    public static function modulosPerfis($modulo_id = null, $perfil_id = null, $empr_id = null, $permission = false, $menu = null)
    {
        $aplicacaoObj = new Aplicacao();

        $sistema = !empty($usuario_id) ? 1 : $_SESSION['usuario']['sistema'];
        $empr_id = !empty($empr_id) ? $empr_id : $_SESSION['empresa']['id'];

        $modulos = null;
        /* Usuário Lidere (sistema = 1). Tem acesso irrestrito a todos os módulos */
        if ($sistema == 1) {
            $modulos = Modulo::where(function ($query) use ($menu, $modulo_id) {
                if (!empty($menu) && $menu != 'A') {
                    $query->where('menu', $menu);
                }
                if (!empty($modulo_id)) {
                    $query->where('modulo_id', $modulo_id);
                } else {
                    $query->whereNull('modulo_id');
                }
            })
                              ->orderBy('ordem', 'asc')
                              ->get()
                              ->toArray();
            if (!empty($modulos)) {
                foreach ($modulos as &$modulo) {
                    if ($permission) {
                        $filter = array();
                        if (!empty($menu) && $menu != 'A') {
                            $filter['m.menu'] = ' = "'.$menu.'"';
                        }
                        $filter['m.id'] = ' = '.$modulo['id'];
                        $filter['mp.perfil_id'] = ' = '.$perfil_id;
                        $filter['mp.empresa_empr_id'] = ' = '.$empr_id;
                        $modulo['check'] = $aplicacaoObj->buscaModuloUsuarioPerfis($filter);
                    }
                    $modulo['sub'] = self::modulosPerfis($modulo['id'], $perfil_id, $empr_id, $permission, ($menu != 'A' ? $menu : null));
                }
            }
        } else {
            $filtro['mp.perfil_id'] = ' = '.$perfil_id;
            $filtro['mp.empresa_empr_id'] = ' = '.$empr_id;
            if (!empty($modulo_id)) {
                $filtro['m.modulo_id'] = ' = '.$modulo_id;
                $modulos = $aplicacaoObj->buscaModulosPerfil($filtro);
            } else {
                $filtro['m.modulo_id'] = ' IS NULL';
                $modulos = $aplicacaoObj->buscaModulosPerfil($filtro);
            }
            if (count($modulos) > 0) {
                foreach ($modulos as &$modulo) {
                    if ($permission) {
                        $filter = array();
                        if (!empty($menu) && $menu != 'A') {
                            $filter['m.menu'] = ' = "'.$menu.'"';
                        }
                        $filter['m.id'] = ' = '.$modulo['id'];
                        $filter['mp.perfil_id'] = ' = '.$perfil_id;
                        $filter['mp.empresa_empr_id'] = ' = '.$empr_id;
                        $modulo['check'] = $aplicacaoObj->buscaModuloUsuarioPerfis($filter);
                    }
                    $modulo['sub'] = self::modulosPerfis($modulo['id'], $perfil_id, $empr_id, $permission, 'A');
                }
            }
        }
        return $modulos;
    }


    public static function menu($modulos = array(), $ativo = null)
    {
        $menu = '';
        if (!empty($modulos)) {
            foreach ($modulos as $modulo) {
                if ($modulo['menu'] == 'S' && (!empty($modulo['check']) || $_SESSION['usuario']['sistema'] == 1)) {
                    if ($modulo['sub'] == null) {
                        if ($modulo['modulo_id'] != null) {
                            $menu .= '<li class="'.($ativo == $modulo['url'] ? 'active': '').'">
                                       <a href="/'.$modulo['url'].'">';
                            $menu .= ' <i class="'.$modulo['icone'].'"></i>';
                            $menu .= ' <span class="font-menu-lidere">   '.$modulo['nome'].'</span>
                                       </a>
                                     </li>';
                        }else{
                            $menu .= '<li class="'.($ativo == $modulo['url'] ? 'active': '').'">
                                   <a href="/'.$modulo['url'].'">';
                            $menu .= ' <i class="'.$modulo['icone'].'"></i>';
                            $menu .= ' <span class="hide-menu">   '.$modulo['nome'].'</span>
                                       </a>
                                     </li>';
                        }
                    } else {
                        $mod = '#'.$ativo;
                        $menu .= '<li class=" '.($mod == $modulo['url'] ? 'active': '').'">
                                    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="'.$modulo['icone'].'"></i><span class="hide-menu">'.$modulo['nome'].'</span>
                                    </a>
                                    <ul aria-expanded="false" class="collapse">';
                                        $menu .= self::menu($modulo['sub'], $ativo);
                        $menu .=    '</ul>
                                  </li>';

                        /*
                                      <a href="#">
            <i class="fa fa-pie-chart"></i>
            <span>Charts</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="pages/charts/chartjs.html"><i class="fa fa-circle-o"></i> ChartJS</a></li>
            <li><a href="pages/charts/morris.html"><i class="fa fa-circle-o"></i> Morris</a></li>
            <li><a href="pages/charts/flot.html"><i class="fa fa-circle-o"></i> Flot</a></li>
            <li><a href="pages/charts/inline.html"><i class="fa fa-circle-o"></i> Inline charts</a></li>
          </ul>

                        */

                    }
                }
            }
        }
        return $menu;
    }

    public static function permissions($empresas = array(), $modulos = array(), $init = true, $sub = 0)
    {
        $permission = '';
        if (!empty($empresas)) {
            if ($init) {
                if (count($empresas) > 1) {
                    $permission .= '<ul class="nav nav-md nav-tabs nav-lines b-info">';
                    foreach ($empresas as $key => $empr) {
                        $permission .= '<li class="'.($key == 0 ? 'active' : null).'">
                                                <a href="javascript:void(0);"
                                                   data-toggle="tab"
                                                   data-target="#tab_'.$empr['id'].'"
                                                   aria-expanded="false">
                                                   '.$empr['nome_fantasia'].'
                                                </a>
                                            </li>';
                    }
                    $permission .= '</ul>';
                }

                $permission .= '<div class="tab-content '.(count($empresas) > 1 ? 'p b-t b-t-2x' : null).'">';
                foreach ($empresas as $key => $empr) {
                    $permission .= '<div role="tabpanel" class="tab-pane animated fadeIn  '.($key == 0 ? 'active' : null).'" id="tab_'.$empr['id'].'">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th width="30"></th>
                                                    <th>Nome do Módulo</th>
                                                    <th>Tipo</th>
                                                    <th width="30%">Tipo de Permissão</th>
                                                </tr>
                                            </thead>
                                            <tbody>';
                    $permission .= self::permissions($empr, $empr['modulos'], false);
                    $permission .=  '       </tbody>
                                        </table>
                                    </div>';
                }
                $permission .= '</div>';
            } else {
                $empr = $empresas;
                foreach ($modulos as $modulo) {
                    if ($sub == 0) {
                        $permission .= '<tr>
                                            <td><div class="checkbox">
                                                    <label class="ui-checks">
                                                        <input type="checkbox"
                                                               class="check-all"
                                                               value="1"
                                                               data-id="'.$modulo['id'].'"
                                                               data-empr-id="'.$empr['id'].'"
                                                               '.($modulo['check'] != false ? 'checked="checked"' : null).'>
                                                        <i></i>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="padding-top17">'.$modulo['nome'].'</td>
                                            <td class="padding-top17">'.($modulo['menu'] == 'S' ? 'Menu' : 'Processo').'</td>
                                            <td>';
                        if ($modulo['menu'] == 'S') {
                            $permission .= '<select name="modulos['.$empr['id'].'#'.$modulo['id'].'#]"
                                                            class="form-control select-all"
                                                            id="'.$modulo['id'].'"
                                                            data-id="'.$modulo['id'].'"
                                                            data-empr-id="'.$empr['id'].'"
                                                            '.($modulo['check'] == false ? 'disabled="disabled"' : null).'
                                                            data-myrules="" style="width: 250px;">
                                                        <option value=""></option>
                                                        <option value="1" '.(!empty($modulo['check']) &&  $modulo['check']['permissao'] == 1 ? 'selected="selected"' : null).'>
                                                            Somente visualização
                                                        </option>
                                                        <option value="2" '.(!empty($modulo['check']) && $modulo['check']['permissao'] == 2 ? 'selected="selected"' : null).'>
                                                            Inclusão e edição
                                                        </option>
                                                        <option value="3" '.(!empty($modulo['check']) &&  $modulo['check']['permissao'] == 3 ? 'selected="selected"' : null).'>
                                                            Gerenciamento completo
                                                        </option>
                                                    </select>';
                        } else {
                            $permission .= '<select name="modulos['.$empr['id'].'#'.$modulo['id'].'#]"
                                                            class="form-control select-all"
                                                            id="'.$modulo['id'].'"
                                                            data-id="'.$modulo['id'].'"
                                                            data-empr-id="'.$empr['id'].'"
                                                            '.($modulo['check'] == false ? 'disabled="disabled"' : null).'
                                                            data-myrules="" style="width: 250px;">
                                                        <option value=""></option>
                                                        <option value="3" '.(!empty($modulo['check']) &&  $modulo['check']['permissao'] == 3 ? 'selected="selected"' : null).'>
                                                            Gerenciamento completo
                                                        </option>
                                                    </select>';
                        }
                        $permission .= '</td>
                                        </tr>';
                    } else {
                        $nbsp = str_repeat('&nbsp;&nbsp;', $sub);
                        $permission .= '<tr>
                                            <td>
                                                <div class="checkbox">
                                                    <label class="ui-checks">
                                                        <input type="checkbox"
                                                               class="check-menu check-'.$modulo['modulo_id'].'"
                                                               value="1"
                                                               data-id="'.$modulo['id'].'"
                                                               data-menu-id="'.$modulo['id'].'"
                                                               data-empr-id="'.$empr['id'].'"
                                                               '.($modulo['check'] != false ? 'checked="checked"' : null).'>
                                                        <i></i>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="padding-top17">'.$nbsp.'&rarr; '.$modulo['nome'].'</td>
                                            <td class="padding-top17">'.($modulo['menu'] == 'S' ? 'Menu' : 'Processo').'</td>
                                            <td>';
                        if ($modulo['menu'] == 'S') {
                            $permission .= '<select name="modulos['.$empr['id'].'#'.$modulo['modulo_id'].'#'.$modulo['id'].']"
                                                            class="form-control select-'.$modulo['modulo_id'].'"
                                                            id="'.$modulo['id'].'"
                                                            data-id="'.$modulo['id'].'"
                                                            data-empr-id="'.$empr['id'].'"
                                                            '.($modulo['check'] == false ? 'disabled="disabled"' : null).'
                                                            data-myrules="" style="width: 250px;">
                                                        <option value=""></option>
                                                        <option value="1" '.(!empty($modulo['check']) && $modulo['check']['permissao'] == 1 ? 'selected="selected"' : null).'>
                                                            Somente visualização
                                                        </option>
                                                        <option value="2" '.(!empty($modulo['check']) && $modulo['check']['permissao'] == 2 ? 'selected="selected"' : null).'>
                                                            Inclusão e edição
                                                        </option>
                                                        <option value="3" '.(!empty($modulo['check']) && $modulo['check']['permissao'] == 3 ? 'selected="selected"' : null).'>
                                                            Gerenciamento completo
                                                        </option>
                                                    </select>';
                        } else {
                            $permission .= '<select name="modulos['.$empr['id'].'#'.$modulo['modulo_id'].'#'.$modulo['id'].']"
                                                            class="form-control select-'.$modulo['modulo_id'].'"
                                                            id="'.$modulo['id'].'"
                                                            data-id="'.$modulo['id'].'"
                                                            data-empr-id="'.$empr['id'].'"
                                                            '.($modulo['check'] == false ? 'disabled="disabled"' : null).'
                                                            data-myrules="" style="width: 250px;">
                                                        <option value=""></option>
                                                        <option value="3" '.(!empty($modulo['check']) && $modulo['check']['permissao'] == 3 ? 'selected="selected"' : null).'>
                                                            Gerenciamento completo
                                                        </option>
                                                    </select>';
                        }
                        $permission .= '</td>
                                        </tr>';
                    }

                    $permission .= self::permissions($empr, $modulo['sub'], false, $sub + 1);
                }
            }
            return $permission;
        }
    }

    /**
     * Insere os logs
     **/
    public static function insereLog($tipo = false, $log, $usuario_id = null, $empresa_id = null)
    {
        $aplicacaoObj = new Aplicacao();

        $tipo = !$tipo ? 'indefinido' : $tipo;
        $log = !$log ? 'indefinido' : $log;
        $usuario_id = !$usuario_id ? 9999 : $usuario_id;
        $empresa_id = !$empresa_id ? 9999 : $empresa_id;

        $data['data'] = self::now();
        $data['tipo'] = $tipo;
        $data['log'] = $log;
        $data['usuario_id'] = $usuario_id;
        $data['empresa_id'] = $empresa_id;
        $aplicacaoObj->insert('tlogs', $data);
    }

    /**
     * Retorna data e hora
     **/
    public static function now()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Gera slug
     **/
    public static function slugify($string)
    {
        $slugifier = new \Slug\Slugifier;
        $slugifier->setDelimiter('.');
        $slugifier->setTransliterate(true);
        return $slugifier->slugify($string);
    }

    /**
     * Conexão para extrair os dados do ERP
     **/
    public static function databaseConn()
    {
        switch (strtoupper(Config::read('DB_EXT'))) {
            case 'ORACLE':
                $conn = self::oracleConn();
                break;
            case 'SQLSERVER':
                $conn = self::sqlserverConn();
                break;
            case 'SQLITE':
                $conn = self::sqliteConn();
                break;
            case 'MYSQL':
                $conn = self::mysqlConn();
                break;
            case 'POSTGRESQL':
                $conn = self::postgresqlConn();
                break;
        }

        return $conn;
    }

    /**
     * Conexão banco Oracle
     **/
    public static function oracleConn()
    {
        $auxiliaresObj = new Auxiliares();
        $empresa = $auxiliaresObj->empresa();

        $string = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = ".$empresa['oracle_host'].")(PORT = ".$empresa['oracle_porta']."))
                    (CONNECT_DATA =
                    (SERVER = DEDICATED)
                    (SERVICE_NAME = ".$empresa['oracle_sid'].")
                    ))";

        if (!isset($conn)) {
            $conn = oci_pconnect($empresa['oracle_usuario'], $empresa['oracle_senha'], $string, 'UTF8');
            if (!$conn) {
                $e = oci_error();
                trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
            }
        }

        $stid = oci_parse($conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/RRRR'");
        oci_execute($stid);

        return $conn;
    }

    public static function oraclePdoOci($empresa_id)
    {
        if (empty(self::$conn[$empresa_id])) {
            $auxiliaresObj = new Auxiliares();
            $empresa = $auxiliaresObj->empresa($empresa_id);

            $tns = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = ".$empresa['oracle_host'].")(PORT = ".$empresa['oracle_porta']."))
                        (CONNECT_DATA =
                        (SERVER = DEDICATED)
                        (SERVICE_NAME = ".$empresa['oracle_sid'].")
                ))";

            try {
                self::$conn[$empresa_id] = new PDO(
                    "oci:dbname=".$tns.";charset=utf8",
                    $empresa['oracle_usuario'],
                    $empresa['oracle_senha']
                );

                //dd(array($tns, $empresa['oracle_usuario'], $empresa['oracle_senha']));
                $stmt = self::$conn[$empresa_id]->prepare("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/RRRR'");
                $stmt->execute();
                $stmt = self::$conn[$empresa_id]->prepare("ALTER SESSION SET NLS_LANGUAGE = 'PORTUGUESE'");
                $stmt->execute();
            } catch (PDOException $e) {
                dd($e->getMessage());
            }
        }

        return self::$conn[$empresa_id];
    }

    /**
     * Conexão banco SQLServer
     **/
    public static function sqlserverConn()
    {
        return null;
    }

    public static function sqliteConn()
    {
        if (empty(self::$conn)) {
            $dsn = 'sqlite:' . Config::read('SQLITE_DB_HOST');

            self::$conn = new PDO($dsn);

            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // // Create table messages
            // $conn->exec("
            //     CREATE TABLE IF NOT EXISTS messages (
            //         id INTEGER PRIMARY KEY,
            //         title TEXT,
            //         message TEXT,
            //         time INTEGER
            //     )");
        }

        return self::$conn;
    }

    /**
     * Conexão banco MySQL
     **/
    public static function mysqlConn()
    {
        return null;
    }

    /**
     * Conexão banco PostgreSQL
     **/
    public static function postgresqlConn()
    {
        return null;
    }

    /**
     * Fecha conexão com o banco
     **/
    public static function databaseClose($result, $conn)
    {
        switch (strtoupper(Config::read('DB_EXT'))) {
            case 'ORACLE':
                self::oracleClose($result, $conn);
                break;
            case 'SQLSERVER':
                self::sqlserverClose($conn);
                break;
            case 'SQLITE':
                self::sqliteClose($conn);
                break;
            case 'MYSQL':
                self::mysqlClose($conn);
                break;
            case 'POSTGRESQL':
                self::postgresqlClose($conn);
                break;
        }

        return true;
    }

    /**
     * Fecha conexão banco Oracle
     **/
    public static function oracleClose($result, $conn)
    {
        oci_free_statement($result);
        oci_close($conn);
    }

    /**
     * Gera nova senha de usuário
     **/
    public static function geraSenha($password)
    {
        $salt = uniqid();
        $str = '6';
        $rounds = '5000';

        $cryptSalt = '$' . $str . '$rounds=' . $rounds . '$' . $salt;
        $hash = crypt($password, $cryptSalt);

        return $hash;
    }

        /**
     * Monta a paginação
     **/

    public static function montaPaginacao($mostra_qtde, $total_tela, $total, $paginas, $atual, $url, $parametros)
    {
        $return = null;
        if ($mostra_qtde) {
            $return['qtde'] = 'Mostrando <strong>'.($total_tela).'</strong> registros de <strong>'.$total.'</strong>';
        }

        $anterior   = $atual - 1;
        $proximo    = $atual + 1;

        $queryString = $parametros != null ? '?'.$parametros : '';

        $html = '<select class="form-control paginacao">';
        for ($i = 1; $i <= $paginas; $i++) {
            $html .= '<option value="'.$url.'/'.$i.$queryString.'" '.($i == $atual ? 'selected="selected"' : '').'>'.$i.'</option>';
        }
        $html .= '</select>';

        if ($paginas <= 1) {
            $html = null;
        }

        $return['paginacao'] = $html;

        return $return;
    }


    /**
     * Conversão de float ou int para R$
     **/
    public static function BRL($value, $pre = null)
    {
        $return = '';
        if (!empty($pre)) {
            $return .= $pre . ' ';
        }
        if (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }
        $return .= number_format($value, 2, ',', '.');
        return $return;
    }

    /**
     * Conversão de moeda para float
     **/
    public static function BRL2Float($value)
    {
        if (strstr($value, ",")) {
            $value = str_replace(".", "", $value);
            $value = str_replace(",", ".", $value);
        }

        if (preg_match("#([0-9\.]+)#", $value, $match)) {
            return floatval($match[0]);
        } else {
            return floatval($value);
        }
    }



    public static function retornaElementosUrl($url)
    {
        if (strpos($url, '/') !== false) {
            $string = explode('/', $url, 3);
            if (isset($string[1])) {
                return $string[1];
            }
        }
        return null;
    }

    /**
     * Transforma ID em Código
     **/
    public static function geraCodigo($id, $qtde = 5)
    {
        return str_pad($id, $qtde, 0, STR_PAD_LEFT);
    }

    public static function data2Date($string = false)
    {
        if (!$string) {
            return;
        }
        $string = implode('-', array_reverse(explode('/', $string)));
        return $string;
    }

    public static function date2Data($string = false, $hora = false)
    {
        if (!$string) {
            return;
        }

        if (strpos($string, ' ') === false) {
            $date = $string;
        } else {
            list($date, $hour) = explode(' ', $string);
        }

        if (!$hora) {
            $string = implode('/', array_reverse(explode('-', $date)));
        } else {
            $string = implode('/', array_reverse(explode('-', $date)));
            $string .= ' '.substr($hour, 0, 5);
        }

        return $string;
    }

     /**
     * Retorna a sequence e já faz o update
     *
     */

    public static function sequencia($seq = false)
    {
        $aplicacaoObj = new Aplicacao();
        $valor = $aplicacaoObj->retornaSequencia($seq, $_SESSION['empresa']['id']);
        $valor = $valor+1;
        $aplicacaoObj->incrementaSequencia($seq);

        return $valor;
    }

    public static function multidimensionalSearchArray($parents, $searched)
    {
        if (empty($searched) || empty($parents)) {
            return false;
        }

        foreach ($parents as $key => $value) {
            $exists = true;
            foreach ($searched as $skey => $svalue) {
                $exists = ($exists && isset($parents[$key][$skey]) && $parents[$key][$skey] == $svalue);
            }
            if ($exists) {
                return $key;
            }
        }

        return false;
    }

    public static function escondeSenha()
    {
        return '***************';
    }

    public static function retornaBcc($emails = false)
    {
        if (!$emails) {
            return false;
        }
        $emails = trim($emails);
        if (strpos($emails, ',') === false) {
            $bcc[] = array('email' => $emails);
        } else {
            $k = explode(',', $emails);
            foreach ($k as $email) {
                $bcc[] = array('email' => $email);
            }
        }
        return $bcc;
    }

    public static function removeElementWithValue($array, $key, $value)
    {
        foreach ($array as $subKey => $subArray) {
            if ($subArray[$key] == $value) {
                unset($array[$subKey]);
            }
        }
        return $array;
    }

    /**
     * Insere diferentes máscaras (CNPJ, CPF, Telefone, CEP)
     */
    public static function insereMascara($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i<=strlen($mask)-1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }
        return $maskared;
    }

    public static function comboEmpresas()
    {
        $empresaObj = new Empresa();
         //combo empresas
        $empresas = $empresaObj->buscaEmpresasEsp($_SESSION['usuario']['id']);
        $empr_id = isset($_SESSION['empresa']['id']) ? $_SESSION['empresa']['id'] : false;

        $erp_id = $_SESSION['empresa']['empr_id'];

        $combo_empresas = Core::montaSelectEmpresas($empresas, $empr_id);
        return $combo_empresas;
    }

    /**
     * Monta o combo de Empresas
     **/
    public static function montaSelectEmpresas($data, $value = false)
    {
        $html = "<select name=\"empr_id\" class=\"form-control combo-empresa\">";
        foreach ($data as $empresa) {
            // se CNPJ = 0, entãéonsolidada, nãdeve considerar no filtro
            $id =  $empresa['id'];
            $html .= "<option value=\"".$id."\" ".($value && $value == $id ? "selected=\"selected\"":"").">".$empresa['nome_fantasia']."</option>";
        }
        $html .= "</select>";
        return $html;
    }

    public static function montaSelectEmpresasNF($data, $value = false)
    {
        $html = "<select name=\"empr_id\" class=\"form-control combo-empresa-nf\">";
        foreach ($data as $empresa) {
            // se CNPJ = 0, entãéonsolidada, nãdeve considerar no filtro
            $id =  $empresa['id'];
            $html .= "<option value=\"".$id."\" ".($value && $value == $id ? "selected=\"selected\"":"").">".$empresa['nome_fantasia']."</option>";
        }
        $html .= "</select>";
        return $html;
    }

    /**
     * Remove os caracteres especiais da string
     **/
    public static function removeCaracteresEspeciais($string = false)
    {
        if (!$string) {
            return false;
        }
        $string = str_replace('/', '', $string);
        $string = str_replace('-', '', $string);
        $string = str_replace('.', '', $string);
        return $string;
    }

    /**
    @purpose Functions referentes ao S3 AWS
     **/
    public static function uploadFileToS3($dir = false, $file = false, $empresa_nfe = false)
    {
        if (!$dir || !$file || !$empresa_nfe) {
            return false;
        }

        // Instantiate the S3 client
        $s3Client = S3Client::factory(array(
            'key'    => Config::read('AWS_ACCESS_KEY_ID'),
            'secret' => Config::read('AWS_SECRET_ACCESS_KEY'),
        ));

        $bucketName = Config::read('AWS_BUCKET_NAME');

        try {
            $s3Client->putObject(array(
                'Bucket' => $bucketName,
                'Key'    => '/'.self::slugify($empresa_nfe['nome_fantasia']).'/'.$file,
                'Body'   => fopen($dir.'/'.$file, 'r'),
                'ACL'    => 'public-read',
            ));

            unlink($dir.'/'.$file);
            return true;
        } catch (S3Exception $e) {
            die($e->getMessage());
            return false;
        }
    }

    public static function checkFileExistS3($file)
    {
        if (!$file) {
            return false;
        }

        $curl = curl_init($file);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        $result = curl_exec($curl);
        $return = false;

        if ($result !== false) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                $return = true;
            }
        }
        curl_close($curl);
        return $return;
    }

    public static function uploadFileToServer($prefix = false, $tmpFile = false, $file = false)
    {

        if (!$tmpFile || !$file) {
            return false;
        }

        $path = 'uploads/'.($prefix ? $prefix.'/' : '');
        if (!is_dir($path)) {
            self::makeUploadDirectory($path);
        }
        move_uploaded_file($tmpFile, $path.$file);
    }

    public static function makeUploadDirectory($path)
    {
        mkdir($path, 0777);
        chmod($path, 0777);
    }

    public static function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public static function enviaEmailPosVendas($link = false)
    {
        if (!$link) {
            return false;
        }

        $auxiliaresObj = new Auxiliares();
        $empresa = $auxiliaresObj->empresa();

        $titulo = 'Portal '.$empresa['nome_fantasia'].' | Pós Vendas - Novo Relatório de Visita';

        $mensagem = file_get_contents(APP_ROOT.'src'.DS.'Resources'.DS.'views'.DS.'emails'.DS.'relatorioViagens.html');
        $mensagem = str_replace('%link%', $link, $mensagem);

        $usuario = self::retornaEmails(Core::parametro('comercial_email_pos_vendas'));
        $bcc = false;

        self::insereFilaEnvioEmail($titulo, $mensagem, $usuario, $bcc, 'portal');
    }


    public static function enviaEmailCoordenacaoMontadoras($html_email)
    {

        if (!$html_email) {
            return false;
        }

        $auxiliaresObj = new Auxiliares();
        $empresa = $auxiliaresObj->empresa();

        $titulo = 'Portal '.$empresa['nome_fantasia'].' | Novo Relatório de Visita - Montadoras';

        $mensagem = file_get_contents(APP_ROOT.'src'.DS.'Resources'.DS.'views'.DS.'emails'.DS.'relatorioViagensCoordenacaoMontadoras.html');
        $mensagem = str_replace('%relatorio%', $html_email, $mensagem);


        $usuario = self::retornaEmails(Core::parametro('comercial_emails_envio_relatorios'));
        $bcc = false;

        self::insereFilaEnvioEmail($titulo, $mensagem, $usuario, $bcc, 'portal',false);
    }

    public static function sizeFilesize($filesize) {

        if( is_numeric($filesize) ){
            $decr = 1024; $step = 0;
            $prefix = array('Byte','KB','MB','GB','TB','PB');

            while( ($filesize / $decr) > 0.9 ){
                $filesize = $filesize / $decr;
                $step++;
            }
            return round($filesize,2).' '.$prefix[$step];
        } else {
            return null;
        }

    }

}
