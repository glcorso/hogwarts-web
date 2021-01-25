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
use Lidere\Config;

/**
 * Cli base para aplicação
 *
 * @package admin
 * @subpackage Cli
 * @category Cli
 * @author Ramon Barros
 */
class Cli extends \Slim\Middleware
{

    public $app;
    public $argv;
    public $params;
    public $settings;

    public function __construct()
    {
        $this->app = Slim::getInstance();
        $this->calledClass = get_called_class();

        $this->class = trim(str_replace('Lidere\Modules', '', $this->calledClass), '\\');
        $this->namespace = explode('\\', str_replace('Controllers\\', '', $this->class));
        list($module, $controller) = $this->namespace;
        $this->module = $module;
        $this->controller = strtolower($controller);

        /**
         * Carrega o nome do template
         */
        $this->template = Config::read('APP_TEMPLATE', '');

        /**
         * Diretório padrão das views src/Resources/views
         */
        $this->viewsResources = APP_ROOT.'src'.DS.'Resources'.DS.'views';

        $this->setPathViews();
        // convert all the command line arguments into a URL
        $this->settings = array();
        $this->params = array();
        $this->argv = $GLOBALS['argv'];
        array_shift($GLOBALS['argv']);
        foreach ($this->argv as $arg) {
            if (strpos($arg, '=') !== false) {
                $param = explode('=', $arg);
                if (strpos($arg, 'setting')  !== false) {
                    if (strpos($param[1], ',') !== false) {
                        $this->settings[$param[0]] = preg_split('/,/', $param[1]);
                    } else {
                        $this->settings[$param[0]] = $param[1];
                    }
                } else {
                    if (strpos($param[1], ',') !== false) {
                        $this->params[$param[0]] = preg_split('/,/', $param[1]);
                    } else {
                        $this->params[$param[0]] = $param[1];
                    }
                }
            } else {
                $this->params[] = $arg;
            }
        }
    }

    /**
     * Verificação de autenticação antes das rotas
     * @return void
     */
    public function call()
    {
        $this->next->call();
    }

    public function log($icon = null, $msg = null, $db = false, $type = 'notify', $usuario_id = null, $empresa_id = null)
    {
        $msg = !empty($msg) ? $msg : null;
        if (php_sapi_name() == 'cli') {
            echo "{$msg}\n";
        }
        dlog('info', $msg);

        if ($db) {
            $logsObj = new \Lidere\Modules\Logs\Models\Log();
            $logsObj->data = date('Y-m-d H:i:s');
            $logsObj->tipo = $type;
            $logsObj->log = $msg;
            $logsObj->icon = $icon;
            $logsObj->usuario_id = !empty($usuario_id) ? $usuario_id : 1;
            $logsObj->empresa_id = !empty($empresa_id) ? $empresa_id : 1;
            $logsObj->save();
        }
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
