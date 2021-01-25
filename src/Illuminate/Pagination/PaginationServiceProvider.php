<?php

namespace Illuminate\Pagination;

use Illuminate\Support\ServiceProvider;

class PaginationServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $request = $this->app['slim']->request;

        Paginator::currentPathResolver(function () use($request) {
            $url = $request->getUrl();
            $url .= $request->getResourceUri();
            return $url;
        });

        Paginator::currentPageResolver(function ($pageName = 'page') use ($request) {
            $page = $request->get($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return $page;
            }

            return 1;
        });
    }
}
