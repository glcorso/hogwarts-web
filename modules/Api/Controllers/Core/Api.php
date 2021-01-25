<?php

namespace Lidere\Modules\Api\Controllers\Core;

use JsonSchema\Validator as JsonSchema;
use Lidere\ChangeLog;
use Lidere\Config;
use Lidere\Controllers\ControllerApi;
use stdClass;

/**
 * Api
 *
 * @package Lidere\Modules
 * @subpackage Api\Controllers\Core
 * @author Ramon Barros
 * @copyright 2017 Lidere Sistemas
 */
class Api extends ControllerApi
{
    /**
     * Url de acesos ao modulo
     * @var boolean
     */
    public $url = false;

    /**
     * Instância do Slim
     * @var \Slim\Slim
     */
    public $app;

    /**
     * Objeto contendo os dados que serão trafegados
     * @var \stdClass
     */
    public $object;

    /**
     * GUID identificador do Conector;
     * @var string
     */
    protected $appId;

    /**
     * Nome do Conector
     * @var string
     */
    protected $appName;

    /**
     * Versão do conector
     * @var string
     */
    protected $appVersion;

    /**
     * Url do Conector
     * @var string
     */
    protected $appUrl;

    /**
     * Verbo HTTP
     * @var string
     */
    protected $appHttpVerb;

    /**
     * Protocolo de de Comunicação
     * @var string
     */
    protected $appProtocol;

    /**
     * Content-Type
     * @var string
     */
    protected $appContentyType;

    /**
     * Desrição da aplicação
     * @var string
     */
    protected $appDescription;

    /**
     * Hash do último commit
     * @var string
     */
    protected $appLastCommit;

    /**
     * Schema Json
     * @var string
     */
    protected $schema = null;

    /**
     * Métodos permitidos para cada rota
     * @var array
     */
    public $methods = array(
          '/'                                         => ['GET', 'POST', 'OPTIONS']
        , '/api/v1'                                   => ['GET', 'POST', 'OPTIONS']
        , '/api/v1/(V|v)alida-serie'                  => ['POST', 'OPTIONS']
        , '/api/v1/(C|c)ategoria-concorrentes'        => ['GET', 'OPTIONS']
        , '/api/v1/(C|c)oncorrentes'                  => ['GET', 'OPTIONS']
        , '/api/v1/(C|c)lientes'                      => ['GET', 'OPTIONS']
        , '/api/v1/(R|r)elatorio-visitas'             => ['GET', 'POST', 'OPTIONS']
        , '/api/v1/(R|r)elatorio-visitas/adicionar'   => ['POST', 'OPTIONS']
        , '/api/v1/(R|r)elatorio-visitas/upload'      => ['POST', 'OPTIONS']
        , '/api/v1/(R|r)evendedores'                  => ['GET', 'OPTIONS']
    );

    /**
     * Api constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->appId = Config::read('APP_ID');
        $this->appName = Config::read('APP_NAME');
        $this->appVersion = VERSION;
        $this->appUrl = BASEURL;
        $this->appHttpVerb = 'POST';
        $this->appProtocol = 'REST';
        $this->appContentyType = 'application/json';
        $this->appDescription = '';
        $this->app->view()->setData('appVersion', $this->appVersion);

        $change = new ChangeLog();
        $this->appLastCommit = $change->lastCommit();

        /**
         * Recupera o pattern da rota
         * @var string
         */
        $router = $this->getRoute()->getPattern();

        /**
         * Recupera os metodos disponíveis para a rota
         * @var array
         */
        $methods = !empty($this->methods[$router]) ? $this->methods[$router] : array();

        /**
         * Retorno para o cliente
         * @var array
         */
        $this->object = array(
            'id' => $this->appId,
            'name' => $this->appName,
            'version' => $this->appVersion,
            'url' => $this->appUrl,
            'httpVerb' => $this->app->request->getMethod(),
            'protocol' => $this->appProtocol,
            'contentyType' => $this->appContentyType,
            'description' => $this->appDescription,
            'router' => $router,
            'httpVerbs' => $methods,
            'lastCommit' => $this->appLastCommit,
        );

        if (!empty($router) && $router == '/api') {
            $this->object['routers'] = $this->methods;
        }
    }

    public function options()
    {
        /**
         * Recupera o pattern da rota
         * @var string
         */
        $router = $this->getRoute()->getPattern();

        /**
         * Recupera os metodos disponíveis para a rota
         * @var array
         */
        $methods = !empty($this->methods[$router]) ? $this->methods[$router] : array();

        /**
         * Retorno para o cliente
         * @var array
         */
        $return = array(
            'router' => $router,
            'method' => $this->app->request->getMethod(),
            'methods' => $methods,
        );

        if (!empty($router) && $router == '/api') {
            $return['routers'] = $this->methods;
        }

        /**
         * Recupera o cabeçalho da requisição
         * @var object
         */
        $response = $this->app->response();
        /**
         * Altera o Content-Type para application/json
         */
        $response['Content-Type'] = 'application/json';

        /**
         * Altera o body para retornar um json
         */
        $response->body(json_encode($return));
    }

    public function setData($data = null)
    {

        if (!empty($data)) {
            $this->object['data'] = $data;
        } else {
            $this->setError('API', 'Vocẽ deve informar os dados de retorno da API.');
        }
        //dlog('debug', print_r($this->object, true));
        return $this;
    }

    /**
     * Define a mensagem de erro que será retornada
     *
     * Exemplo:
     * <code>
     *  {
     *      "error": {
     *          "code": "API143",
     *          "message": "Vocẽ deve informar os dados de retorno da API.",
     *          "fields": []
     *      }
     *  }
     * </code>
     *
     * @param string $code Define um código para identificação do erro Ex.: Nome da classe e linha do erro
     * @param string $message Mensagem de erro para exibir
     * @param array $fields Array contendo as mensagens de cada campo.
     *
     * @return Api
     * @throws \ReflectionException
     */
    public function createError(string $code, string $message = '', array $fields = [], $exception = null)
    {
        $error = new stdClass();
        $error->code = $code;
        $error->message = $message;
        $error->fields = $fields;
        if (APP_DEBUG) {
            $error->debug_backtrace = db(1);
            $error->code .= $error->debug_backtrace['line'];
            dlog('error', print_r($error, true));
        }
        return $error;
    }

    /**
     * Define a mensagem de erro que será retornada
     *
     * <code>
     *  {
     *      "error": {
     *          "code": "API143",
     *          "message": "Vocẽ deve informar os dados de retorno da API.",
     *          "fields": []
     *      }
     *  }
     * </code>
     *
     * @param string $code Define um código para identificação do erro Ex.: Nome da classe e linha do erro
     * @param string $message Mensagem de erro para exibir
     * @param array $fields Array contendo as mensagens de cada campo.
     *
     * @return Api
     * @throws \ReflectionException
     */
    public function setError(
        string $code,
        string $message = '',
        array $fields = [],
        $exception = null) {
        $this->object['error'] = $this->createError($code, $message, $fields, $exception);
        return $this;
    }

    /**
     * Metodo chamado nas rotas
     *
     * @access public
     *
     * @apiVersion 1.0.0
     * @apiName Lidere
     * @apiGroup Api
     * @api {get} /
     * @apiDescription Esse serviço ou método REST é quem dirá quais o métodos e quais os modelos de dados serão
     * trafegados e/ou estarão disponíveis para integrar com outros sistemas.
     * @apiUse Erro404
     * @apiErrorExample {json} Error-Response 404:
     * HTTP/1.1 404 Not Found
     * {
     *   "error": "Not Found"
     * }
     */
    public function index()
    {
        $this->response();
    }

    public function response($status = 200)
    {
        /**
         * Recupera o cabeçalho da requisição
         * @var object
         */
        $response = $this->app->response();

        /**
         * Define o status de retorno
         */
        $response->status($status);

        /**
         * Altera o Content-Type para application/json
         */
        $response['Content-Type'] = 'application/json';

        /**
         * Altera o body para retornar um json
         */
        $response->body($this->toJson());
    }

    /**
     * @return \stdClass
     */
    public function toObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toObject());
    }

    /**
     * @param string $date
     * @return bool|\DateTime|string
     */
    public function date($date = '')
    {
        $d = null;
        if (!empty($date)) {
            $d = \DateTime::createFromFormat('d/m/Y', $date);
            if ($d == false) {
                $d = \DateTime::createFromFormat('Y-m-d', $date);
            }
            if ($d == false) {
                $d = \DateTime::createFromFormat('d-m-Y', $date);
            }
            $d = $d->format('Y-m-d\TH:i:s-03:00');
        }
        return $d;
    }

    /**
     * Recupera os dados da rota
     * @return object
     */
    public function getRoute()
    {
        $this->route = $this->app->router()->getCurrentRoute();
        return !empty($this->route) ? $this->route : null;
    }

    public function validateSchema($data = null, $schema = null)
    {
        $validator = new JsonSchema();
        $validator->validate($data, (object) ['$ref' => 'file://' . realpath($schema)]);

        $errors = $validator->getErrors();
        if (!empty($errors)) {
            foreach ($validator->getErrors() as $error) {
                $this->errors[] = sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
        }

        return $validator->isValid();

    }

    // ======================

    public function genericUpdate()
    {
        $requestBody = $this->getRequestBodyData();
        $service = $this->app->service;

        $this->returnResponseServer(
            $service->updateEntity($requestBody)
        );
    }

    public function genericDelete()
    {
        $requestBody = $this->getRequestBodyData();
        $service = $this->app->service;

        $this->returnResponseServer(
            $service->deleteEntity($requestBody->id)
        );
    }

    public function genericRead($idEntity = null)
    {

        $urlParams = $this->app->request->get();

        $service = $this->app->service;

        $response = $idEntity ?
        $service->findById($idEntity) :
        $service->findByFilters($urlParams);

        $this->setData($response->data)
             ->response(200);

    }

    public function create()
    {
        $entity = $this->getRequestBodyData();
        $status = 201;
        $data = [];
        $schema = $this->getValidationSchema();
        if ($this->validateSchema($entity, $schema)) {
            $this->returnResponseServer(
                $this->app->service->createEntity($entity)
            );
        } else {
            $this->setError(
                'API513',
                'Não foi possível criar o recurso!',
                $this->errors
            );
            $this->response(400);
        }
    }

    public function returnResponseServer($response)
    {
        if ($response->statusCode == 200 || $response->statusCode == 201) {
            $this->setData(
                $response->data
            );
        } else {
            $this->setError(
                'API532',
                $response->errorMessage
            );
        }
        $this->response($response->statusCode);
    }

    // =======================

    protected function getValidationSchema(): string
    {
        return APP_ROOT . 'modules' . DS . 'Api' . DS . 'Json' . DS . 'Schema' . DS . $this->schema;
    }

    protected function getRequestBodyData()
    {
        $json = $this->app->request()->getBody();
        $requestData = !empty($json) ? json_decode($json) : [];
        return $requestData;
    }
}
