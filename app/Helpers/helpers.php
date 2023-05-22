<?php

use App\Helpers\Session\FileSession;
use App\Helpers\Session\SessionInterface;
use App\Helpers\Utils\ActivityHistory;
use App\Helpers\Utils\GetSchool;
use App\Providers\AuthManager;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use JetBrains\PhpStorm\Pure;

/**
 * This is only for `function not exists` in config/swoole_http.php.
 */
if (!function_exists('swoole_cpu_num')) {
    function swoole_cpu_num(): int
    {
        return 1;
    }
}

/**
 * This is only for `function not exists` in config/swoole_http.php.
 */
if (!defined('SWOOLE_SOCK_TCP')) {
    define('SWOOLE_SOCK_TCP', 1);
}

if (!defined('SWOOLE_PROCESS')) {
    define('SWOOLE_PROCESS', 2);
}

if (!function_exists('school')) {
    /**
     * @return GetSchool|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function school(): ?GetSchool
    {
        if (!auth()->check()) {
            return GetSchool::singleton(request()->get('school'), request()->get('school'));
        }

        $key = auth()->user()->getRememberToken() ?? null;

        if ($key) {
            return GetSchool::singleton($key);
        }

        return null;
    }
}

if (!function_exists('activity_history')) {
    /**
     * @return ActivityHistory
     * @throws Exception
     */
    function activity_history(): ActivityHistory
    {
        return ActivityHistory::singleton();
    }
}

if (!function_exists('base64_url_encode')) {
    /**
     * @param string $data
     * @return string
     */
    #[Pure]
    function base64_url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64_url_decode')) {
    /**
     * @param string $data
     * @return string
     */
    #[Pure]
    function base64_url_decode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}

if (!function_exists('pascal_case')) {
    /**
     * @param string $str
     * @return string
     */
    function pascal_case(string $str): string
    {
        return ucfirst(preg_replace_callback('/[^a-z]([a-z])/i', function ($matches) {
            return strtoupper($matches[1]);
        }, $str));
    }
}

if (!function_exists('snake_to_camel')) {
    /**
     * @param string $str
     * @return string
     */
    function snake_to_camel(string $str): string
    {
        return lcfirst(preg_replace_callback('/_([^_])/', function ($m) {
            return strtoupper($m[1]);
        }, strtolower($str)));
    }
}

if (!function_exists('json_response')) {
    /**
     * @param bool $result
     * @param $data
     * @param $errors
     * @param int $status
     * @return JsonResponse
     */
    function json_response(bool $result, $data = [], $errors = [], $status = 200): JsonResponse
    {
        return response()->json([
            'successful'   => $result,
            'responseData' => is_array($data) && isset($data['data']) && 1 === count($data) ? $data['data'] : $data,
            'errors'       => is_array($data) && isset($errors['errors']) && 1 === count($errors) ? $errors['errors'] : $errors,
            'sessionId'    => session()->sessionId(),
        ], $status);
    }
}

if (!function_exists('session')) {
    /**
     * @return FileSession
     */
    function session(): SessionInterface
    {
        return app(SessionInterface::class);
    }
}

if (!function_exists('convert_str')) {
    /**
     * @param $str
     * @return string|string[]|null
     */
    function convert_str($str)
    {
        $str = str_replace('ền', 'en', $str);
        $str = preg_replace("/(ũ)/", "u", $str);
        $str = preg_replace("/(ằ|ả|ạ)/", "a", $str);
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
        $str = preg_replace("/(đ)/", "d", $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
        $str = preg_replace("/(Đ)/", "D", $str);
        return $str;
    }
}

if (!function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param ArrayAccess|array $array
     * @param string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if (!function_exists('ampReplaceXML')) {
    /**
     * @param string|null $string $string
     * @return string
     */
    function ampReplaceXML(?string $string): string
    {
        return str_replace('& ', '&amp; ', $string ?? '');
    }
}

if (!function_exists('get_pg_date')) {
    /**
     * @param string|null $date
     * @param null $default
     * @return string|null
     */
    function get_pg_date(?string $date, $default = NULL): ?string
    {
        if (preg_match('/^(\d?\d)[\/\-\.\s](\d?\d)[\/\-\.\s](\d{4})$/', (string)$date, $matches)) {
            return "'{$matches[3]}-{$matches[2]}-{$matches[1]}'::DATE";
        }

        return $default;
    }
}

if (!function_exists('get_pg_escape_string')) {
    /**
     * @param string|null $string $string
     * @param string|null $cast
     * @return string
     */
    function get_pg_escape_string(?string $string, string $cast = null): string
    {
        return "'" . pg_escape_string($string ?? '') . ($cast ? "'::" . $cast : "'");
    }
}

if (!function_exists('get_pg_jsonb')) {
    /**
     * @param string|null $string $string
     * @param string|null $cast
     * @return string
     */
    function get_pg_jsonb(array $data): string
    {
        $keys   = array_keys($data);
        $values = array_map('pg_escape_string', array_values($data));
        return "'" . json_encode(array_combine($keys, $values)) . "'::JSONB";
    }
}

if (!function_exists('remove_non_printable_characters')) {
    /**
     * @param string $string
     * @return string
     */
    function remove_non_printable_characters(string $string): string
    {
        return (string)preg_replace('/[\x00-\x1F\x7F]/', '', $string);
    }
}

if (!function_exists('get_carbon_vn')) {
    /**
     * @param string|null $date
     * @param false $default_now
     * @return Carbon|null
     */
    function get_carbon_vn(?string $date, $default_now = false): ?Carbon
    {   
        if (preg_match('/^(\d?\d)[\/\-\.\s](\d?\d)[\/\-\.\s](\d{4})$/', (string)$date, $matches)) {
            $carbon = Carbon::create($matches[3], $matches[2], $matches[1]);
            $value  = sprintf('%02d%02d%04d', $matches[1], $matches[2], $matches[3]);
            if (($carbon instanceof Carbon) && $carbon->format('dmY') === $value) {
                return $carbon;
            }
        }

        if ($default_now) {
            return Carbon::now();
        }

        return null;
    }
}

if (!function_exists('token_download_generate')) {
    /**
     * @param int $time
     * @return string
     */
    function token_download_generate(int $time): string
    {
        $header    = base64_url_encode(json_encode([AuthManager::generateBearer(), time() + $time]));
        $signature = hash_hmac(AuthManager::algoHash(), $header, AuthManager::getSecretKey());
        return "$signature.$header";
    }
}

if (!function_exists('countSubarrayMatches')) {
    /**
     * @param array $multiArray
     * @param array $checkArray
     * @return int
     */
    function countSubarrayMatches(array $multiArray,array $checkArray): int
    {
        return array_reduce($multiArray, function ($carry, $subArray) use ($checkArray){
            $matchPairs = array_intersect_assoc($subArray,$checkArray);
            return $carry + (count($matchPairs) === count($checkArray));
        },0);
    }
}

if (!function_exists('success_response')) {

    /**
     * @param mixed $errors
     * @param int $status
     * @param array $data
     * @return JsonResponse
     */
    function success_response(array $data = [], int $status = 200, mixed $errors = []): JsonResponse
    {
        return json_response(true, $data, $errors, $status);
    }
}

if (!function_exists('errors_response')) {

    /**
     * @param mixed $errors
     * @param int $status
     * @param array $data
     * @return JsonResponse
     */
    function errors_response(mixed $errors = [], int $status = 400, array $data = []): JsonResponse
    {
        return json_response(false, $data, $errors, $status);
    }
}

if (!function_exists('token_form_register')) {
    /**
     * @param array $params
     * @return string
    */
    function token_form_register(array $params): string
    {
        $token = AuthManager::tokenRegister($params);
        return $token;
    }
}

if (!function_exists('log_debug')) {
    /**
     * @param string $message
     * @param array $context
     */
    function log_debug(string $message, array $context = []): void
    {
        if (!(bool)env('APP_DEBUG')) {
            return;
        }

        \Illuminate\Support\Facades\Log::debug($message, $context);
    }
}

if (!function_exists('throw_json_response')) {
    /**
     * @param $errors
     */
    function throw_json_response ($errors)
    {
        throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $errors]));
    }
}
if (!function_exists('throw_exception')) {
    /**
     * @param $errors
     * @throws Exception
     */
    function throw_exception($errors)
    {
        throw new \Exception($errors);
    }
}