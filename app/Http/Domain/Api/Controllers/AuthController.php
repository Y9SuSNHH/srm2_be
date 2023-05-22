<?php

namespace App\Http\Domain\Api\Controllers;

use App\Eloquent\BlacklistToken;
use App\Eloquent\School as EloquentSchool;
use App\Eloquent\User as EloquentUser;
use App\Http\Domain\Api\Requests\Auth\LoginRequest;
use App\Http\Enum\RoleAuthority;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller;

/**
 * Class AuthController
 * @package App\Http\Domain\Api\Controllers
 */
class AuthController extends Controller
{
    const TIME_LIFE = 3600;

    /**
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->throwJsonIfFailed();
            session()->create_sid();
            session()->start();
            /** @var EloquentUser $user */
            $user = EloquentUser::query()->with('roles:id,authority')->where('username', $request->username)->first();
            /** @var EloquentSchool|null $school */
            $school = $this->getSchool($user, $request->school);

            if (!$user || !$school || !Hash::check($request->password, $user->password)) {
                return json_response(false, [], ['message' => 'Unauthorized'], 401);
            }

            [$token, $expired] = $this->getToken($user->id, $school->school_code ?? '');

            return json_response(true, [
                'token' => $token,
                'expires' => $expired,
                'expiresIn' => Carbon::now()->addSeconds($expired)->toAtomString(),
            ]);
        } catch (\Throwable $e) {
            return json_response(false , [], (array)$e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function changePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, [
            'current_password'      => 'required|string|min:6',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);

        /** @var EloquentUser $user */
        $user = EloquentUser::query()->findOrFail(auth()->getId());

        if (!$user || !Hash::check($request->input('current_password'), $user->password)) {
            return json_response(false, [], ['message' => 'Mật khẩu hiện tại không đúng.']);
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
            'updated_by' => auth()->getId(),
        ]);

        return json_response(true);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function authInfo(): \Illuminate\Http\JsonResponse
    {
        try {
            /** @var \App\Http\Domain\Api\Models\Auth\User $user */
            $user = auth()->guard()->user();

            return json_response(true, [
                'isPrivilege' => auth()->guard()->isPrivilege(),
                'roleAuthority' => auth()->guard()->roleAuthority(),
                'user' => $user
            ]);
        } catch (\Throwable $exception) {
            return json_response(false , [], $exception->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */

    public function authInfoAndRefreshToken(): \Illuminate\Http\JsonResponse
    {
        try {
            /** @var \App\Http\Domain\Api\Models\Auth\User $user */
            $user = auth()->guard()->user();

            /** @var EloquentSchool|null $school */
            $school = $this->getSchool($user->getEloquentUser(), school()->getId());

            if (!$user || !$school) {
                return json_response(false, [], ['message' => 'Unauthorized'], 401);
            }

            [$token, $expired] = $this->getToken($user->id, $school->school_code ?? '');

            return json_response(true, [
                'token' => $token,
                'isPrivilege' => auth()->guard()->isPrivilege(),
                'expires' => $expired,
                'expiresIn' => Carbon::now()->addSeconds($expired)->toAtomString(),
                'user' => $user
            ]);
        } catch (\Throwable $exception) {
            return json_response(false , [], $exception->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): \Illuminate\Http\JsonResponse
    {
        try {
            BlacklistToken::query()->create(['signature' => auth()->user()->getRememberToken()]);

            return json_response(true);
        } catch (\Throwable $exception) {
            return json_response(false);
        }
    }

    /**
     * @param int $user_id
     * @param string $school_code
     * @return array
     * @throws \Exception
     */
    private function getToken(int $user_id, string $school_code): array
    {
        $secret_file = realpath(config('app.secret_path'));
        $secret = $secret_file ? file_get_contents($secret_file) : null;
        $jwt_secret = $secret ?: env('JWT_SECRET');

        if (!$jwt_secret) {
            throw new \Exception('Token secret is unavailable!');
        }

        $key = session()->sessionId();

        if (!$key) {
            throw new \Exception('The session is not created!', ['key' => null]);
        }

        $algo = 'sha256';
        $expired = session()->maxLifeTime();
        $time = time() + $expired;
        session()->set('ss_time', $time);
        $type = 'bearer';
        $header = base64_url_encode(json_encode(compact('algo', 'key', 'type')));
        $payload = base64_url_encode(json_encode([
            'id' => $user_id,
            'school' => $school_code,
        ]));
        $signature = hash_hmac($algo, "$key.$payload", $jwt_secret);
        session()->set('$user_id', $user_id);

        return ["$header.$payload.$signature", $expired];
    }

    /**
     * @param EloquentUser|null $user
     * @param $school
     * @return object|null
     */
    private function getSchool(?EloquentUser $user, $school): ?object
    {
        if (!$user) {
            return null;
        }

        $roles = $user->roles->pluck('authority')->toArray();

        if (!$school && array_intersect($roles, RoleAuthority::withoutChooseSchool())) {
            return (object)[];
        }

        return EloquentSchool::query()->orWhere('school_code', $school)->orWhere('id', (int)$school)->first();
    }
}
