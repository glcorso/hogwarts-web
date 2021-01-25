<?php
namespace Lidere\Modules\Services;

use Illuminate\Support\ServiceProvider;

class ServicesServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $request = $this->getRequest();
        $this->app->bind('service', function ($app) use ($request) {
            $service = str_replace('Controllers', 'Services', $app->slim->calledClass);
            if (!class_exists($service)) {
                throw new \Exception("O Service {$service} não existe!");
            }
            $usuario = !empty($app->slim->session['usuario'])
                     ? $app->slim->session['usuario']
                     : null;
            $empresa = !empty($app->slim->session['empresa'])
                     ? $app->slim->session['empresa']
                     : null;
            $modulo = !empty($app->slim->session['modulo'])
                     ? $app->slim->session['modulo']
                     : null;
            return new $service(
                $usuario,
                $empresa,
                $modulo,
                $app->slim->data,
                $request
            );
        });
    }

    private function getRequest()
    {
        $request = [];
        if ($this->app->slim->request->isGet()) {
            $request = $this->app->slim->request()->get();
        } elseif ($this->app->slim->request->isPost()) {
            $request = $this->app->slim->request()->post();
        } elseif ($this->app->slim->request->isPut()) {
            $request = $this->app->slim->request()->put();
        } elseif ($this->app->slim->request->isDelete()) {
            $request = $this->app->slim->request()->delete();
        } elseif ($this->app->slim->request->isHead()) {
            $request = $this->app->slim->request()->head();
        } elseif ($this->app->slim->request->isOptions()) {
            $request = null;
        } elseif ($this->app->slim->request->isPatch()) {
            $request = $this->app->slim->request()->patch();
        }
        return $request;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('service');
    }
}
