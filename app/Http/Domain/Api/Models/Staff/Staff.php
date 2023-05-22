<?php

namespace App\Http\Domain\Api\Models\Staff;

use App\Helpers\Json;
use App\Helpers\Traits\CamelArrayAble;

/**
 * Class Staff
 * @package App\Http\Domain\Api\Models\Staff
 */
class Staff extends Json
{
    use CamelArrayAble;

    public $id;
    public $fullname;
    public $email;
    public $team;
    public $day_off;
    public $status;
    public $user;
}