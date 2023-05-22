<?php

namespace App;

class Application extends \Laravel\Lumen\Application
{
    public const BASE_DOMAIN_NAMESPACE = 'App\\Http\\Domain\\';

    private $provider_regitered;

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @return void
     */
    public function register($provider)
    {
        if (!is_array($this->provider_regitered)) {
            $this->provider_regitered = [];
        }

        if (is_string($provider)) {
            if (in_array($provider, $this->provider_regitered)) {
                return;
            }

            $this->provider_regitered[] = $provider;

        }

        parent::register($provider);
    }

    /**
     * @param string $domain
     * @return void
     */
    public function registerRepositoryServiceProvider(string $domain)
    {
        $repository_service_provider = Application::BASE_DOMAIN_NAMESPACE . pascal_case($domain) . '\\Providers\RepositoryServiceProvider';
        $filename = base_path(lcfirst(str_replace('\\', '/', $repository_service_provider)).'.php');

        if ($filename && file_exists($filename) && is_file($filename)) {
            $this->register($repository_service_provider);
        }
    }

    /**
     * @param string $class_name
     * @param array $parameters
     * @return mixed
     */
    public function service(string $class_name, array $parameters = []): mixed
    {
        if (str_starts_with($class_name, self::BASE_DOMAIN_NAMESPACE)) {
            $arr = explode('\\', str_replace(self::BASE_DOMAIN_NAMESPACE, '', $class_name));
            $this->registerRepositoryServiceProvider($arr[0]);

            return empty($parameters) ? app($class_name) : app($class_name, $parameters);
        }

        return null;
    }

    public function configAuth()
    {
        app()->configure('auth');
        app()->singleton('auth', fn ($app) => new \App\Providers\AuthManager(app()));
    }
}

