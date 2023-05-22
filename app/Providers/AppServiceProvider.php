<?php

namespace App\Providers;

use App\Application;
use App\Helpers\LengthAwarePaginator;
use App\Http\Enum\PerPage;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRepositoryProviders();
    }

    public function boot()
    {
        $this->registerSession();
        $this->loadDomainRouter();
        $this->builderMacro();
    }

    /**
     * Register All Repository services.
     *
     * @return void
     */
    protected function registerRepositoryProviders()
    {
        $providers = config('app.providers', []);

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }

    }

    /**
     * Load The Application Routes
     * Next we will include the routes file so that they can all be added to the application.
     * This will provide all of the URLs the application can respond to, as well as the controllers that may handle them.
     */
    protected function loadDomainRouter()
    {
        /** @var \Laravel\Lumen\Application $app */
        $app = $this->app;
        $domains = config('app.domains', []);

        foreach ($domains as $key => $group) {
            $dirname = pascal_case($key);
            $path = realpath($app->basePath(lcfirst(str_replace('\\', '/', Application::BASE_DOMAIN_NAMESPACE)).  $dirname .'/router.php'));

            if (!$path) {
                continue;
            }

            $app->router->group(array_filter(array_replace([
                'namespace' => Application::BASE_DOMAIN_NAMESPACE. $dirname .'\\Controllers'
            ], $group, [
                'prefix' => preg_replace('/(^web$)|[^a-z0-9\-_]*/', '', strtolower($key))
            ])), function ($router) use ($path, $app) {
                /** @var \Laravel\Lumen\Routing\Router $router */
                require_once $path;
            });
        }

        $app->router->options('/{route:.*}', ['middleware' => \App\Http\Middleware\CorsMiddleware::class, function () {
            return json_encode(func_get_args());
        }]);
    }

    /**
     * @return void
     */
    protected function builderMacro(): void
    {
        Builder::macro('whereJsonB', function($column, $attribute, $search) {
            /** @var Builder $this */
            $search = \pg_escape_string($search);
            return $this->whereRaw("$column->>'$attribute'='$search'");
        });

        Builder::macro('whereILike', function($column, $search) {
            /** @var Builder $this */
            return $this->where($column, 'iLIKE', "%{$search}%");
        });

        Builder::macro('orWhereILike', function($column, $search) {
            /** @var Builder $this */
            return $this->orWhere($column, 'iLIKE', "%{$search}%");
        });

        Builder::macro('makePaginate', function($per_page = null, $columns = ['*'], $page_name = 'page', $page = null) {
            $page = $page ?: Paginator::resolveCurrentPage($page_name);

            $total = $this->toBase()->getCountForPagination();

            $per_page = ($per_page instanceof \Closure
                ? $per_page($total)
                : $per_page
            ) ?: $this->model->getPerPage();

            if (!$per_page) {
                $per_page = PerPage::getDefault();
            }

            $results = $total
                ? $this->forPage($page, $per_page)->get($columns)
                : $this->model->newCollection();
            return Container::getInstance()->makeWith(LengthAwarePaginator::class, [
                'items' => $results, 'total' => $total, 'perPage' => $per_page, 'currentPage' => $page, 'options' => [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => $page_name,
                ]
            ]);
        });

    }

    protected function registerSession()
    {
        $session = request()->header('X-SESSION') ?? null;

        if ($session) {
            $cond = (string)$session;

            if ('1' === $cond || 'true' === $cond) {
                session()->create_sid();
                session()->start();
            } else {
                session()->start($session);
            }
        }
    }
}
