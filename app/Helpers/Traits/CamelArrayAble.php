<?php

namespace App\Helpers\Traits;

/**
 * Trait CamelArrayAble
 * @package App\Helpers\Traits
 */
trait CamelArrayAble
{
    /**
     * @param array $filter
     * @return array
     */
    public function toArray(array $filter = []): array
    {
        $keys = array_keys(get_object_vars($this));

        if (0 < count($filter)) {
            $keys = array_intersect($keys, $filter);
        }

        $arr = [];

        foreach ($keys as $key) {
            if (property_exists($this, $key)) {
                $key_camel = snake_to_camel($key);
                $arr[$key_camel] = $this->{$key};
            }
        }

        return $arr;
    }

    /**
     * @param array $array
     * @return array
     */
    public function toCamelArray(array $array): array
    {
        $arr = [];

        foreach ($array as $key => $value) {
            $key_camel = snake_to_camel($key);
            $arr[$key_camel] = is_array($value) ? $this->toCamelArray($value) : $value;
        }

        return $arr;
    }
}