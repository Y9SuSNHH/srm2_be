<?php

namespace App\Http\Domain\Api\Repositories\Register;

use Illuminate\Http\Request;

interface RegisterRepositoryInterface
{
    /**
     * Register
     * @return array
     */
    public function register(array $request) :array;

}
