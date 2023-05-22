<?php

namespace App\Http\Enum;

use Carbon\Carbon;

class LockDay
{
    public const WEEK_1_RATING = 10;
    public const WEEK_4_RATING = 25;

    /**
     * @param Carbon $day
     * @return bool
     */
    public static function isLockWeek1Rating(Carbon $day = null): bool
    {
        return false;
        if (!$day) {
            return true;
        }

        $now = Carbon::now();
        return $now->isBefore($day) || $now->isAfter($day->copy()->addDay(LockDay::WEEK_1_RATING)->setTime(23, 55));
    }

    public static function isLockWeek4Rating(Carbon $day = null): bool
    {
        return false;
        if (!$day) {
            return true;
        }

        $now = Carbon::now();
        return $now->isBefore($day->copy()->addDay(LockDay::WEEK_1_RATING+1)->startOfDay()) || $now->isAfter($day->copy()->addDay(LockDay::WEEK_4_RATING)->setTime(23, 55));
    }
}
