<?php
namespace Lidere\Controllers;

use Exception;
use Lidere\Cache;
use Lidere\Config;
use Lidere\Models\Usuario;
use Slim\Middleware;
use Slim\Slim;

class ControllerApi extends Middleware
{
    /**
     * Instância da aplicação
     *
     * @var \Slim\Slim
     */
    public $app;

    /**
     * Recebe o token para autenticação com a API igual ao definido no
     * arquivo .env em APP_KEY
     * @var string
     */
    public $http_x_token;

    /**
     * Recebe o token do usuário do portal que efetuou o login
     * @var string
     */
    public $http_x_usuario;

    /**
     * Recebe o token da empresa do portal que o usuário efetuou o login
     * @var string
     */
    public $http_x_empresa;

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
     * Define o diretório das views que deve ser carregado
     */
    public function __construct()
    {
        $this->app = Slim::getInstance();

        $this->calledClass = get_called_class();

        if ($this->calledClass != 'Lidere\Controllers\ControllerApi') {

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
            // dd($this->class, $this->namespace, $this->module, $this->controller);

            /**
             * Carrega o nome do template
             */
            $this->template = Config::read('APP_TEMPLATE', '');

            /**
             * Diretório padrão das views src/Resources/views
             */
            $this->viewsResources = APP_ROOT . 'src' . DS . 'Resources' . DS . 'views';

            if (!$this->app->request()->isAjax()) {
                /**
                 * Define as views do sistema
                 */
                $this->setPathViews();
            }

            $this->usuario = !empty($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
            $this->empresa = !empty($_SESSION['empresa']) ? $_SESSION['empresa'] : null;

            $this->data['modulo'] = $this->modulo;
            $this->data['permissao'] = $this->permissao;
            $this->data['voltar'] = $this->app->request->getReferer();

            $this->app->slim->session = $_SESSION;
            $this->app->slim->data = $this->data;
            $this->app->slim->empresa = $this->empresa;
            $this->app->slim->usuario = $this->usuario;
        }

    }

    private function allowRoute()
    {
        $pathinfo = $this->app->request()->getPathInfo();
        return !in_array(
            $pathinfo,
            array(
                '/',
                '/welcome'
            )
        ) && strpos($pathinfo, '/assets') === false
          && strpos($pathinfo, '/api') !== false
          && strpos($pathinfo, '/libs') !== false;
    }

    /**
     * Verificação de autenticação antes das rotas
     * @return void
     */
    public function call()
    {
        $pathinfo = $this->app->request()->getPathInfo();
        if (!$this->app->request()->isOptions() && !$this->getAuth() && $this->allowRoute()) {

            $dbt0 = db(0);
            $dbt1 = db(1);
            $this->object['error'] = new \stdClass();
            $this->object['error']->code = 'AUTH';
            $this->object['error']->message = 'Autenticação inválida!';
            $this->object['error']->fields = [];
            if (APP_DEBUG) {
                $this->object['error']->debug_backtrace = $dbt1;
                dlog('error', print_r($this->object['error']->debug_backtrace, true));
            }
            $this->object['error']->code .= $dbt0['line'];
            dlog('info', print_r($this->object, true));
            /**
             * Recupera o cabeçalho da requisição
             * @var object
             */
            $response = $this->app->response();

            /**
             * Define o status de retorno
             */
            $response->status(403);

            /**
             * Altera o Content-Type para application/json
             */
            $response['Content-Type'] = 'application/json';

            /**
             * Altera o body para retornar um json
             */
            $response->body(json_encode($this->object));
        } else {
            // try {
            $this->next->call();
            // } catch (\Exception $e) {
            // Redireciona para src/Slim/Expection/Error
            // $this->app->error($e);
            // }
        }
    }

    /**
     * Valida o token para liberação do Octopus
     * @return bool
     */
    public function getAuth()
    {
        $pathinfo = $this->app->request()->getPathInfo();

        $api_login = json_decode(Config::read('API_LOGIN'));

        $this->http_x_token = $this->app->request->headers->get('X-Token');

        $this->http_x_empresa = $this->app->request->headers->get('X-Empresa');

        $this->http_x_usuario = $this->app->request->headers->get('X-Usuario');

        dlog('debug', 'http_x_token:' . $this->http_x_token);
        dlog('debug', 'http_x_empresa:' . $this->http_x_empresa);
        dlog('debug', 'http_x_usuario:' . $this->http_x_usuario);

        $auth = false;
        if (!empty($this->http_x_token)) {

            // Recupera o token da aplicação
            $app_key = Config::read('APP_ID', '');

            // Verifica se o token da aplicação é o mesmo
            if ($this->http_x_token == $app_key) {
                // Se a rota for login autentica
                if (in_array($pathinfo, (array) $api_login)) {
                    $auth = true;
                } elseif (!empty($this->http_x_usuario) && !empty($this->http_x_empresa)) {
                    // Define um token unico para a API + Usuário + Empresa e salva no cache
                    $cache = md5($this->http_x_token . $this->http_x_usuario . $this->http_x_empresa);

                    // Se o cache não existe cria
                    if (!Cache::has('api.auth.' . $cache)) {
                        // Efetua a autenticação atraves do token do usuário e empresa
                        $user = Usuario::auth(
                            $this->http_x_usuario,
                            $this->http_x_empresa
                        );
                        if (!empty($user)) {
                            $usuario = $user->toArray();
                            Cache::put('api.auth.' . $cache, $usuario, 1);
                        }
                    } else {
                        $usuario = Cache::get('api.auth.' . $cache);
                    }

                    if (!empty($usuario)) {

                        $this->usuario = $_SESSION['usuario'] = [
                            'id' => $usuario['id'],
                            'nome' => $usuario['nome'],
                            'email' => $usuario['email'],
                            'situacao' => $usuario['situacao'],
                            'data_criacao' => $usuario['data_criacao'],
                            'data_edicao' => $usuario['data_edicao'],
                            'sistema' => $usuario['sistema'],
                            'tipo' => $usuario['tipo'],
                            'empresa_id' => $usuario['empresa_padrao_id'],
                            'ad' => $usuario['ad'],
                            'token' => $usuario['token'],
                        ];

                        $this->empresa = $_SESSION['empresa'] = [
                            'id' => $usuario['empresa_id'],
                            'razao_social' => $usuario['razao_social'],
                            'nome_fantasia' => $usuario['nome_fantasia'],
                            'dominio' => $usuario['dominio'],
                            'diretorio' => $usuario['diretorio'],
                            'cor_principal' => $usuario['cor_principal'],
                            'api_token' => $usuario['token_empresa'],
                            'empr_id' => $usuario['empr_id'],
                        ];
                        $auth = true;
                    } else {
                        dlog('error', 'Não foi possível efetuar a autenticação via token!');
                    }
                }
            }
        }

        return $auth;

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
     * @see \Illuminate\Validation\Validator
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
     *
     * @param array $options
     * @param bool $full
     * @return void
     */
    public function redirect(
        $options = array(
            'pagina' => 1,
            'queryString' => true,
        ),
        $full = true
    ) {
        $redirect = '';
        $url = '/' . $this->modulo['url'];
        if (is_array($options)) {
            if (!empty($options['pagina'])) {
                $pagina = (is_numeric($options['pagina']) ? $options['pagina'] : 1);
                $redirect .= '/pagina/' . (string) $pagina;
            }
            if (!empty($options['queryString'])) {
                $redirect .= '?' . $_SERVER['QUERY_STRING'];
            }
        } elseif (is_string($options)) {
            if ($full) {
                $url = $options;
                $redirect = '';
            } else {
                $redirect .= '/' . $options;
            }
        }

        $this->app->redirect($url . $redirect);
    }

    /**
     * Define os diretórios das views
     * Default: src/Resources/views
     * Modulos: modules/<modulo>/Views/
     * Modulos: modules/<modulo>/Views/<controller>
     *
     * @return void
     * @throws Exception
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
        if (!empty($this->template) && is_dir($this->viewsResources . DS . $this->template)) {
            $loader->addPath($this->viewsResources . DS . $this->template);
        }

        $loader->prependPath('/tmp');

        /**
         * Adicionado diretório de views do modulo
         */
        if (is_dir(APP_ROOT . 'modules' . DS . $this->module . DS . 'Views')) {
            $loader->prependPath(APP_ROOT . 'modules' . DS . $this->module . DS . 'Views');
        } elseif (is_dir(APP_ROOT . 'src' . DS . 'Modules' . DS . $this->module . DS . 'Views')) {
            $loader->prependPath(APP_ROOT . 'src' . DS . 'Modules' . DS . $this->module . DS . 'Views');
        } else {
            throw new \Exception("Você deve criar o diretório Views dentro do modulo {$this->module}!");
        }

        /**
         * Adiciona o diretório de veiws do modulo + controller
         */
        if (is_dir(APP_ROOT . 'modules' . DS . $this->module . DS . 'Views' . DS . $this->controller)) {
            $loader->prependPath(APP_ROOT . 'modules' . DS . $this->module . DS . 'Views' . DS . $this->controller);
        } elseif (is_dir(APP_ROOT . 'src' . DS . 'Modules' . DS . $this->module . DS . 'Views' . DS . $this->controller)) {
            $loader->prependPath(APP_ROOT . 'src' . DS . 'Modules' . DS . $this->module . DS . 'Views' . DS . $this->controller);
        }
    }
}
