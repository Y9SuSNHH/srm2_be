<?php

namespace App\Helpers;

use Illuminate\Http\Exceptions\HttpResponseException;

abstract class Json implements \JsonSerializable, \Stringable
{
    /**
     * Json constructor.
     * @param array $argument
     */
    public function __construct($argument = null)
    {
        if (is_string($argument)) {
            if (!$argument = json_decode($argument, true)) {
                return;
            }
        }

        if (is_object($argument) && is_callable([$argument, 'toArray'])) {
            $argument = $argument->toArray();
        }

        if (is_array($argument)) {
            $keys = array_keys(get_class_vars(get_called_class()));

            foreach ($keys as $key) {
                $this->{$key} = array_key_exists($key, $argument) ? $argument[$key] : null;
            }
        }
    }

    /**
     * @param array|null $argument
     */
    public function refresh(?array $argument): void
    {
        foreach (array_keys(get_class_vars(get_called_class())) as $key) {
            if (array_key_exists($key, $argument)) {
                $this->{$key} = $argument[$key];
            } elseif (null === $argument) {
                $this->{$key} = null;
            }
        }
    }

    /**
     * @param string $name
     * @return null|mixed
     */
    public function __get(string $name)
    {
        if (preg_match('/^(.+)_date$/', $name, $matches) && property_exists($this, $matches[1])) {
            $name = $matches[1];
        }

        if (property_exists($this, $name)) {
            if (in_array($name, static::dates()) || array_key_exists($name, static::dates())) {
                return $this->formatDate($name, $this->{$name});
            }

            return $this->{$name};
        }
        return null;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function __set(string $name, $value): void
    {
        false;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        $arr = get_object_vars($this);
        $keys = array_diff(array_keys($arr), static::hidden());
        $array = [];

        foreach ($keys as $key) {
            $key_camel = snake_to_camel($key);

            if (in_array($key, static::dates()) || array_key_exists($key, static::dates())) {
                $array[$key_camel] = $this->formatDate($key, $arr[$key]);
            } else {
                $array[$key_camel] = is_array($arr[$key]) ? $this->subTransform($arr[$key], $key) : $arr[$key];
            }
        }

        return $array;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->jsonSerialize();
    }

    /**
     * The attributes that should be mutated to dates
     *
     * @return array [key, key.sub_key, key.*.sub_key, key.sub_key.*.sub_sub_key]
     */
    public static function dates(): array
    {
        return [];
    }

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @return array
     */
    public static function hidden(): array
    {
        return [];
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        $data = [];

        foreach (get_object_vars($this) as $key => $value) {
            if (is_object($value) && is_callable([$value, 'toJson'])) {
                $value = json_decode($value->toJson($options), true);
            }

            $data[$key] = $value;
        }

        $json = json_encode($data, $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpResponseException(response()->json(['successful' => false, 'errors' => json_last_error_msg()]));
        }

        return $json;
    }

    /**
     * @param $name
     * @param $value
     * @return string|null
     */
    private function formatDate($name, $value): ?string
    {
        /** @var \Carbon\Carbon|null $date */
        $date = ($value instanceof \Carbon\Carbon) ? $value : (is_string($value) ? \Carbon\Carbon::parse($value) : null);

        if ($date && array_key_exists($name, static::dates())) {
            return $date->format(static::dates()[$name]);
        }

        return !$date ? null : $date->toAtomString();
    }

    /**
     * @param array $arr
     * @param string $prefix
     * @return array
     */
    private function subTransform(array $arr, string $prefix): array
    {
        $array = [];

        foreach ($arr as $key => $value) {
            $key_camel = snake_to_camel($key);
            $key = $prefix .'.'. preg_replace('/^\d+$/', '*', $key);

            if (in_array($key, static::dates()) || array_key_exists($key, static::dates())) {
                $array[$key_camel] = $this->formatDate($key, $value);
            } else {
                $array[$key_camel] = is_array($value) ? $this->subTransform($value, $key) : $value;
            }
        }

        return $array;
    }
}
