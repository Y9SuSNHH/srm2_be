<?php

namespace App\Http\Domain\Api\Controllers;

use App\Http\Domain\Api\Repositories\Register\RegisterRepositoryInterface;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use App\Eloquent\BlacklistToken;
use App\Eloquent\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Class RegisterController
 * @package App\Http\Domain\Api\Controllers
 */
class RegisterController extends Controller
{
    private RegisterRepositoryInterface $register_repository;

    /**
     * @param RegisterRepositoryInterface $register_repository
     */
    public function __construct(RegisterRepositoryInterface $register_repository)
    {
        $this->register_repository = $register_repository;
    }

    public function handleRegister(RegisterRepositoryInterface $register_repository, Request $request): JsonResponse
    {
        $bearer = $request->header('Authorization') ?: $request->input('token');
        $token_part = explode('.', substr($bearer, strlen('bearer ')));
        $header = json_decode(base64_url_decode($token_part[0] ?? ''), true);
        $payload = $token_part[1] ?? '';
        $signature = $token_part[2] ?? null;
        $blacklist = !$signature ? null : BlacklistToken::query()->where('signature', $signature)->first(['id']);

        if (!$blacklist) {
            $algo = $header['algo'] ?? '';
            $secret_file = realpath(config('app.secret_path'));
            $secret = $secret_file ? file_get_contents($secret_file) : null;
            $jwt_secret = $secret ?: env('JWT_SECRET');

            if ($algo && hash_hmac($algo, "$payload", $jwt_secret) === $signature) {
                $data = json_decode(base64_url_decode($payload), true);
                $contact = DB::table('sv50_contacts')->where('id', $data['contact'])->where('staff_id', $data['staff'])->first(['id']);
                if($contact){
                    $request['staff_id'] = $data['staff'];
                    $request['block_token'] = $signature;
                    $request = $request->all();
                    return json_response(true, $register_repository->register($request));
                }
                return response()->json(['successful' => false, 'errors' => 'URL expired']);
            }
        }

        return response()->json(['successful' => false, 'errors' => 'Token blocked']);
    }

}