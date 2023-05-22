<?php

namespace App\Providers;

use App\Eloquent\BlacklistToken;
use App\Eloquent\User;
use App\Helpers\Utils\GetSchool;
use App\Http\Domain\Api\Models\Auth\User as AuthUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        app()->configAuth();
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $bearer = $request->headers->has('Authorization') ? $request->header('Authorization') : $this->getTokenUrl($request);

            if ($bearer) {
                [$payload, $signature] = $this->verifyToken($bearer);

                if ($payload && $signature) {
                    /** @var User $user */
                    $user = User::query()->find($payload['id'] ?? 0);

                    if ($user) {
                        GetSchool::singleton($signature, $payload['school'] ?? null);
                        return new AuthUser($user, $signature);
                    }

                    return new AuthUser(null, null);
                }
            }

            return null;
        });
    }

    /**
     * @param $request
     * @return string|null
     */
    protected function getTokenUrl($request): ?string
    {
        /** @var \Laravel\Lumen\Http\Request $request */
        $token = $request->get('token');
        [$signature, $header] = $token ? explode('.', $token) : [null, null];

        if (hash_hmac(AuthManager::algoHash(), $header, AuthManager::getSecretKey()) === $signature) {
            [$bearer, $time] = json_decode(base64_url_decode($header ?? ''), true);

            if ($time > time()) {
                return $bearer;
            }
        }

        return null;
    }

    /**
     * @param string $bearer
     * @return mixed
     */
    protected function verifyToken(string $bearer): mixed
    {
        $token_part = explode('.', substr($bearer, strlen('bearer ')));
        $header = json_decode(base64_url_decode($token_part[0] ?? ''), true);
        $payload = $token_part[1] ?? '';
        $signature = $token_part[2] ?? null;
        $blacklist = !$signature ? null : BlacklistToken::query()->where('signature', $signature)->first(['id']);

        if (!$blacklist) {
            $key = $header['key'] ?? null;
            session()->start($key);
            $time = session()->get('ss_time', 0);
            $algo = $header['algo'] ?? '';
            $secret_file = realpath(config('app.secret_path'));
            $secret = $secret_file ? file_get_contents($secret_file) : null;
            $jwt_secret = $secret ?: env('JWT_SECRET');

            if ((time() <= $time) && $algo && hash_hmac($algo, "$key.$payload", $jwt_secret) === $signature) {
                session()->set('ss_time', time() + session()->maxLifeTime());
                return [json_decode(base64_url_decode($payload), true), $signature];
            }
        }

        return [null, null];
    }
}
