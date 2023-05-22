<?php

namespace App\Http\Domain\Common\Model\Backlog;

use App\Helpers\Json;
use App\Http\Enum\WorkDiv;
use App\Http\Enum\WorkStatus;

/**
 * Class Backlog
 * @package App\Http\Domain\Common\Model\Backlog
 *
 * @property int $id
 * @property int $user_id
 * @property int $school_id
 * @property int $work_div
 * @property $work_div_name
 * @property int $work_status
 * @property $work_status_name
 * @property array $work_payload
 * @property Reference $reference
 * @property $note
 * @property $created_at
 * @property $updated_at
 *
 */
class Backlog extends Json
{
    public $id;
    public $user_id;
    public $school_id;
    public $work_div;
    public $work_div_name;
    public $work_status;
    public $work_status_name;
    public $work_payload;
    public $reference;
    public $note;
    public $created_at;
    public $updated_at;

    /**
     * Backlog constructor.
     * @param null $argument
     * @throws \ReflectionException
     */
    public function __construct($argument = null)
    {
        /** @var \App\Eloquent\Backlog $argument */
        $work_div_name = WorkDiv::from($argument->work_div)->getLang();
        $work_status_name = WorkStatus::from($argument->work_status)->getLang();
        $work_payload = null;
        $reference = null;

        if ($argument->work_payload) {
            $work_payload = json_decode($argument->work_payload, true);
        }

        if ($argument->reference) {
            $reference = new Reference(json_decode($argument->reference, true));
        }

        parent::__construct(array_merge($argument->toArray(), [
                'work_div_name' => $work_div_name,
                'work_status_name' => $work_status_name,
                'work_payload' => $work_payload,
                'reference' => $reference,
            ]));
    }

    public static function dates(): array
    {
        return [
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @return \Carbon\Carbon|null
     */
    public function getCreatedAt(): ?\Carbon\Carbon
    {
        if ($this->created_at instanceof \Carbon\Carbon) {
            return $this->created_at;
        }

        if (is_string($this->created_at)) {
            $date = \Carbon\Carbon::parse($this->created_at);
            return $date->isValid() ? $date : null;
        }

        return null;
    }
}
