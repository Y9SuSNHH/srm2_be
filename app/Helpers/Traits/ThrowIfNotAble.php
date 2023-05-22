<?php

namespace App\Helpers\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

trait ThrowIfNotAble
{
    /**
     * @param array|string $models
     * @param callable $callable
     * @param bool $is_throw_response
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function createAble(array|string $models, callable $callable, bool $is_throw_response = false): mixed
    {
        if (is_string($models)) {
            $models = [$models];
        }

        foreach ($models as $model) {
            if (!auth()->guard()->creatable($model)) {
                if ($is_throw_response) {
                    throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => "You don't have permission to create record for this model: $model"]));
                }

                throw new \Exception("You don't have permission to create record for this model: $model");
            }
        }

        if (is_callable($callable)) {
            return $callable();
        }

        return null;
    }

    /**
     * @param array|string $models
     * @param callable $callable
     * @param bool $is_throw_response
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function updateAble(array|string $models, callable $callable, bool $is_throw_response = false): mixed
    {
        if (is_string($models)) {
            $models = [$models];
        }

        foreach ($models as $model) {
            if (!auth()->guard()->editable($model)) {
                if ($is_throw_response) {
                    throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => "You don't have permission to edit record for this model: $model"]));
                }

                throw new \Exception("You don't have permission to edit record for this model: $model");
            }
        }

        if (is_callable($callable)) {
            return $callable();
        }

        return null;
    }

    /**
     * @param array|string $models
     * @param callable $callable
     * @param bool $is_throw_response
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function deleteAble(array|string $models, callable $callable, bool $is_throw_response = false): mixed
    {
        if (is_string($models)) {
            $models = [$models];
        }

        foreach ($models as $model) {
            if (!auth()->guard()->deletable($model)) {
                if ($is_throw_response) {
                    throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => "You don't have permission to delete record for this model: $model"]));
                }

                throw new \Exception("You don't have permission to delete record for this model: $model");
            }
        }

        if (is_callable($callable)) {
            return $callable();
        }

        return null;
    }
}
