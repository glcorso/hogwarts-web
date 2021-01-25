<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Controllers;

use Slim\Slim;
use Lidere\Core;
use Lidere\Config;
use Lidere\Models\Aplicacao;
use \Lidere\Models\Usuario;

/**
 * Controller base para aplicação
 *
 * @package admin
 * @subpackage Controller
 * @category Controller
 * @author Ramon Barros
 */
class Controller extends \Slim\Middleware
{
    /**
     * Instância da aplicação
     *
     * @var \Slim\Slim
     */
    public $app;

    public $route;

    /**
     * Erros gerados pelo Validate
     *
     * @var array
     */
    public $errors;

    public $segment = 1;

    /**
     * Classe que extende o Controller
     *
     * @var string
     */
    public $class;

    /**
     * O namespace da classe que extende o Controller
     *
     * @var string
     */
    public $namespace;

    /**
     * Nome do modulo atual
     *
     * @var string
     */
    public $module;

    /**
     * Dados de rota do modulo
     * @var array
     */
    public $modulo;

    /**
     * Permissões do usuário para o modulo
     * @var array
     */
    public $permissao;

    /**
     * Controller atual
     *
     * @var string
     */
    public $controller;

    /**
     * Template default do sistema
     *
     * @var string
     */
    public $template;

    /**
     * Diretório padrão das views
     *
     * @var string
     */
    public $viewsResources;

    /**
     * Sessão do usuário
     * @var array
     */
    protected $usuario;

    /**
     * Sessão da empresa
     * @var array
     */
    protected $empresa;

    /**
     * Dados enviados para a view
     * @var array
     */
    protected $data;

    /**
     * Define a url do modulo e se o mesmo necessita estar logado.
     * @var string
     */
    protected $url;

    /**
     * Define o diretório das views que deve ser carregado
     */
    public function __construct()
    {
        $this->app = Slim::getInstance();

        $this->calledClass = get_called_class();

        $this->class = trim(str_replace('Lidere\Modules', '', $this->calledClass), '\\');
        $this->namespace = explode('\\', str_replace('Controllers\\', '', $this->class));
        list($module, $controller) = $this->namespace;
        $this->module = $module;
        $this->controller = strtolower($controller);

        $this->app->slim->calledClass = $this->calledClass;
        $this->app->slim->class = $this->class;
        $this->app->slim->namespace = $this->namespace;
        $this->app->slim->module = $this->module;
        $this->app->slim->controller = $this->controller;

        /**
         * Carrega o nome do template
         */
        $this->template = Config::read('APP_TEMPLATE', '');

        /**
         * Diretório padrão das views src/Resources/views
         */
        $this->viewsResources = APP_ROOT.'src'.DS.'Resources'.DS.'views';

        if (!$this->app->request()->isAjax()) {
            /**
             * Define as views do sistema
             */
            $this->setPathViews();

            $this->auth();
        }

        $this->app->slim->session = $_SESSION;
        $this->usuario = !empty($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
        $this->empresa = !empty($_SESSION['empresa']) ? $_SESSION['empresa'] : null;

        $this->data['modulo'] = $this->modulo;
        $this->data['permissao'] = $this->permissao;
        $this->data['voltar'] = $this->app->request->getReferer();
        $this->app->slim->data = $this->data;
    }

    /**
     * Verificação de autenticação antes das rotas
     * @return void
     */
    public function call()
    {
        $this->next->call();
    }

    public function auth()
    {
        $classVars = get_class_vars($this->calledClass);
        if (isset($this->url) && is_string($this->url)) {
            if ($this->url != 'login') {
                if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
                    $this->redirect('/login');
                }
            }
            $aplicacaoObj = new Aplicacao();




            $this->modulo = $aplicacaoObj->buscaModulo(array('m.url' => $this->url));
            if (!empty($this->modulo['url'])) {
                Core::validaAcessoModulo($this->app, $this->modulo['url']);

                if (!empty($_SESSION['usuario'])) {
                    $usuario = \Lidere\Models\Usuario::find($_SESSION['usuario']['id']);
                    if(empty($usuario['perfil_id'])){
                        $this->permissao = $usuario->modulo()
                                                   ->select(
                                                       'tmodulos.*',
                                                       'tmodulos_usuarios.permissao',
                                                       'tmodulos_usuarios.empresa_empr_id'
                                                   )
                                                   ->whereId($this->modulo['id'])
                                                   ->first();
                        $this->permissao = !empty($this->permissao) ? $this->permissao->toArray() : array();
                        if (!$this->permissao && $_SESSION['usuario']['sistema'] == '1') {
                            $this->permissao['permissao'] = 3;
                        }
                    }else{
                        $this->permissao = $usuario->perfil->moduloPerfil()
                                                   ->select(
                                                       'tmodulos.*',
                                                       'tmodulos_perfil.permissao',
                                                       'tmodulos_perfil.empresa_empr_id'
                                                   )
                                                   ->whereRaw('tmodulos.id = '.$this->modulo['id'])
                                                   ->first();

                        $this->permissao = !empty($this->permissao) ? $this->permissao->toArray() : array();
                        if (!$this->permissao && $_SESSION['usuario']['sistema'] == '1') {
                            $this->permissao['permissao'] = 3;
                        }

                    }
                }
            } else {
                throw new \Exception("Erro - Crie o modulo ".$this->calledClass.' na tabela tmodulos!');
            }
        } elseif (!isset($this->url)) {
            throw new \Exception("Erro - Crie uma propriedade public \$url em ".$this->calledClass.'!');
        }
    }

    /**
     * Recupeara os dados da requisição conforme method
     * @return mixed
     */
    public function input()
    {
        $get = $this->app->request()->get();
        $post = $this->app->request()->post();
        $body = $this->app->request()->getBody();
        if (!empty($get)) {
            return $get;
        } elseif (!empty($post)) {
            return $post;
        } else {
            return $body;
        }
    }

    /**
     * Validação dos dados enviados
     * @see Illuminate\Validation\Validator
     * @param  array  $rules Regras de validação
     * @param  array  $data  Dados para validação
     * @param  array $messages Mensagem de exibição para o usuário
     * @return boolean
     */
    public function validate($rules = array(), $data = array(), $messages = array())
    {
        // make a new validator object
        $v = $this->app->validator->make($data, $rules, $messages);

        // check for failure
        if ($v->fails()) {
            // set errors and return false
            $this->errors = $v->errors()->all();
            return false;
        }

        // validation pass
        return true;
    }

    public function index()
    {
        $this->redirect();
    }

    /**
     * Redireciona para a página principal do modulo
     * @return void
     */
    public function redirect($options = array(
        'pagina' => 1,
        'queryString' => true
    ), $full = true)
    {
        $redirect = '';
        $url = '/'.$this->modulo['url'];
        if (is_array($options)) {
            if (!empty($options['pagina'])) {
                $pagina = (is_numeric($options['pagina']) ? $options['pagina'] : 1);
                $redirect .= '/pagina/'.(string)$pagina;
            }
            if (!empty($options['queryString'])) {
                $redirect .= '?'.$_SERVER['QUERY_STRING'];
            }
        } elseif (is_string($options)) {
            if ($full) {
                $url = $options;
                $redirect = '';
            } else {
                $redirect .= '/'.$options;
            }
        }

        $this->app->redirect($url.$redirect);
    }


    /**
     * Retorna a requisão como Json
     *
     * @param mixed $return Mensagem de retorno
     *
     * @return void
     */
    public function withJson($return = null)
    {
        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    /**
     * Define os diretórios das views
     * Default: src/Resources/views
     * Modulos: modules/<modulo>/Views/
     * Modulos: modules/<modulo>/Views/<controller>
     *
     * @return void
     */
    private function setPathViews()
    {
        // Get an instance of the Twig Environment
        $twig = $this->app->view->getInstance();

        // From that get the Twig Loader instance (file loader in this case)
        $loader = $twig->getLoader();

        /**
         * Define o diretório de views padrão
         */
        $loader->setPaths($this->viewsResources);

        /**
         * Adicionado diretório do template
         */
        if (!empty($this->template) && is_dir($this->viewsResources.DS.$this->template)) {
            $loader->addPath($this->viewsResources.DS.$this->template);
        }

        /**
         * Adicionado diretório de views do modulo
         */
        if (is_dir(APP_ROOT.'modules'.DS.$this->module.DS.'Views')) {
            $loader->prependPath(APP_ROOT.'modules'.DS.$this->module.DS.'Views');
        } elseif (is_dir(APP_ROOT.'src'.DS.'Modules'.DS.$this->module.DS.'Views')) {
            $loader->prependPath(APP_ROOT.'src'.DS.'Modules'.DS.$this->module.DS.'Views');
        } else {
            throw new \Exception("Você deve criar o diretório Views dentro do modulo {$this->module}!");
        }

        /**
         * Adiciona o diretório de veiws do modulo + controller
         */
        if (is_dir(APP_ROOT.'modules'.DS.$this->module.DS.'Views'.DS.$this->controller)) {
            $loader->prependPath(APP_ROOT.'modules'.DS.$this->module.DS.'Views'.DS.$this->controller);
        } elseif (is_dir(APP_ROOT.'src'.DS.'Modules'.DS.$this->module.DS.'Views'.DS.$this->controller)) {
            $loader->prependPath(APP_ROOT.'src'.DS.'Modules'.DS.$this->module.DS.'Views'.DS.$this->controller);
        }
    }
}
