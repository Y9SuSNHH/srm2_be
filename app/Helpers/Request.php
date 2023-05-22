<?php

namespace App\Helpers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\ValidationException;

/**
 * Class Validator
 *
 * @package App\Helpers
 */
abstract class Request
{
    public const CAST_BOOL = 'bool';
    public const CAST_INT = 'int';
    public const CAST_STRING = 'string';
    public const CAST_FLOAT = 'float';
    public const CAST_ARRAY = 'array';
    public const CAST_CARBON = 'carbon';

    /** @var int */
    private $_;
    /** @var array */
    private $all;
    /** @var \Illuminate\Validation\Validator|null */
    private $validator;
    /** @var array */
    private $validated;
    /** @var HttpRequest */
    private $http_request;
    /** @var array */
    protected $casts;
    /** @var array */
    private static $caches = [];

    /**
     * Request constructor.
     * @throws AuthenticationException
     */
    public function __construct()
    {
        $this->http_request = app(HttpRequest::class);
        
        if (!$this->authorize()) {
            throw new AuthenticationException();
        }

        $arr = $this->http_request->all();
        array_walk_recursive($arr, function(&$v) {
            if (is_string($v)) {
                $v = trim($v);
            }
        });
        $this->all = $arr;
        $this->validator = null;
        $this->validated = [];
        $this->_ = time();
        static::$caches[$this->_] = [];

    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (!key_exists($name, self::$caches[$this->_])) {
            self::$caches[$this->_][$name] = isset($this->validated[$name]) ? $this->castData($this->validated[$name], $name) : (isset($this->all[$name]) ? $this->castData($this->all[$name], $name) : null);
        }
        return self::$caches[$this->_][$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function empty(string $name): bool
    {
        return empty($this->__get($name));
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $keys = array_keys($this->rules([]));
        $all = array_intersect_key($this->all, array_combine($keys, $keys));
        return array_replace(array_fill_keys($keys, null), $all);
    }

    /**
     * @param array $input
     * @return array
     */
    public function prepareInput(array $input): array
    {
        return $input;
    }

    /**
     * @param array $input
     * @return array
     */
    abstract public function rules(array $input): array;

    /**
     * Get attribute list
     *
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Get message list
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Return json if validator failed
     * @throws \Exception
     */
    public function throwJsonIfFailed()
    {
        $this->createValidator();
        if ($this->validator->fails()) {
            $errors = (new ValidationException($this->validator))->errors();
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $errors]));
        } else {
            $this->validated = $this->validator->validated();
        }
    }

    /**
     * @param array $filter
     * @return array
     * @throws ValidationException
     * @throws \Exception
     */
    public function validated(array $filter = [])
    {
        $this->createValidator();

        if (0 === count($filter)) {
            return $this->validator->validated();
        }

        return array_intersect_key($this->validator->validated(), array_combine($filter, $filter));
    }

    /**
     * @return HttpRequest|\Laravel\Lumen\Application|mixed
     */
    protected function httpRequest(): mixed
    {
        return $this->http_request;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function createValidator(): void
    {
        if (!$this->validator) {
            $input = $this->prepareInput($this->all);
            $rules = $this->rules($input);
            $messages = $this->messages();
            $attributes = $this->attributes();
            $this->validator = validator($input, $rules, $messages, $attributes);
            if (method_exists($this, 'addSometimes')) {
                $this->addSometimes();
            }
        }

        if (!$this->validator) {
            throw new \Exception('create validator fail');
        }
    }

    /**
     * @param $value
     * @param $key
     * @return mixed
     */
    private function castData($value, $key): mixed
    {
        if (null === $value) {
            return null;
        }

        if (!is_array($this->casts) || !key_exists($key, $this->casts)) {
            return $value;
        }

        switch ($this->casts[$key]) {
            case self::CAST_BOOL:
                return (bool)$value;
            case self::CAST_INT:
                return (int)$value;
            case self::CAST_FLOAT:
                return (float)$value;
            case self::CAST_STRING:
                return (string)$value;
            case self::CAST_ARRAY:
                return (array)$value;
            case self::CAST_CARBON:
                if ($value instanceof \Carbon\Carbon) {
                    return $value;
                }

                try {
                    return \Carbon\Carbon::parse($value);
                } catch (\Throwable $e) {
                    return null;
                }
            default:
                return $value;
        }
    }
    /**
     * @return array
     */
    public function all(): array
    {
        return $this->all;
    }

}
