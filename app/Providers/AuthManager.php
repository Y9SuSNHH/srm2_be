<?php

namespace App\Providers;

use App\Helpers\Utils\GetSchool;
use Illuminate\Auth\AuthManager as IlluminateAuthManager;

class AuthManager extends IlluminateAuthManager
{
    /** @var string|null */
    private $fake_default_guard;

    /**
     * Replace register a new callback based request guard.
     *
     * @param string $driver
     * @param callable $callback
     * @return AuthManager
     */
    public function viaRequest($driver, callable $callback): AuthManager
    {
        return $this->extend($driver, function () use ($callback) {
            $guard = new RequestGuard($callback, $this->app['request'], $this->createUserProvider());

            $this->app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }

    /**
     * @param $name
     * @param $config
     * @return RequestGuard
     */
    public function createEloquentDriver($name, $config): RequestGuard
    {
        $guard = new RequestGuard(function () use ($name, $config) {
            $key = session()->sessionId();

            if (!$key) {
                $key = session()->create_sid();
            }

            $model = app($config['model']);
            $model = isset($config['id']) ? $model->find($config['id']) : $model->where('username', $config['username'])->first() ;
            $s = AuthManager::generateBearer([$key, $model->id, $config['school']]);
            $signature = explode('.', $s)[2];
            GetSchool::singleton($signature, $config['school'] ?? null);

            return new \App\Http\Domain\Api\Models\Auth\User($model, $signature);
        }, $this->app['request'], $this->createUserProvider());

        $this->app->refresh('request', $guard, 'setRequest');

        return $guard;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        if (is_callable([$this, 'user'])) {
            return ((object)$this->user())->id ?? null;
        }

        return null;
    }

    /**
     * @param $name
     * @return AuthManager
     */
    public function fakeDefaultsGuard($name): AuthManager
    {
        $this->fake_default_guard = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->fake_default_guard ?? $this->app['config']['auth.defaults.guard'];
    }

    /**
     * @param null $name
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard|RequestGuard
     */
    public function guard($name = null)
    {
        return parent::guard($name);
    }

    /**
     * @return string
     */
    public static function algoHash(): string
    {
        return 'sha256';
    }

    /**
     * @return string|null
     */
    public static function getSecretKey(): ?string
    {
        $secret_file = realpath(config('app.secret_path'));
        $secret = $secret_file ? file_get_contents($secret_file) : null;
        $key = $secret ?: env('JWT_SECRET');

        return $key ? (string)$key : null;
    }

    /**
     * @param array|null $params
     * @return string
     */
    public static function generateBearer(array $params = null): string
    {
        $jwt_secret = self::getSecretKey();

        if (!$jwt_secret) {
            return 'bearer ';
        }

        [$key, $auth_id, $school_code] = $params;
        $key = $key ?? session()->sessionId();
        $header = base64_url_encode(json_encode(['algo' => self::algoHash(), 'key' => $key, 'type' => 'bearer']));
        $payload = base64_url_encode(json_encode([
            'id' => $auth_id ?? auth()->getId(),
            'school' => $school_code ?? school()->getCode(),
        ]));
        $signature = hash_hmac(self::algoHash(), "$key.$payload", $jwt_secret);

        return "bearer $header.$payload.$signature";
    }

    public static function tokenRegister(array $params = null): string
    {
        $jwt_secret = self::getSecretKey();

        if (!$jwt_secret) {
            return '';
        }

        [$contact_id, $staff_id] = $params;
        $header = base64_url_encode(json_encode(['algo' => self::algoHash(), 'type' => 'bearer']));
        $payload = base64_url_encode(json_encode([
            'contact' => $contact_id ?? '',
            'staff' => $staff_id ?? '',
        ]));
        $signature = hash_hmac(self::algoHash(), "$payload", $jwt_secret);

        return "$header.$payload.$signature";
    }

}
