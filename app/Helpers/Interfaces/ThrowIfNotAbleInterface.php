<?php

namespace App\Helpers\Interfaces;

interface ThrowIfNotAbleInterface
{
    /**
     * @param array|string $models
     * @param callable $callable
     * @return mixed
     */
    public function createAble(array|string $models, callable $callable): mixed;

    /**
     * @param array|string $models
     * @param callable $callable
     * @return mixed
     */
    public function updateAble(array|string $models, callable $callable): mixed;

    /**
     * @param array|string $models
     * @param callable $callable
     * @return mixed
     */
    public function deleteAble(array|string $models, callable $callable): mixed;
}