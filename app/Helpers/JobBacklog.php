<?php

namespace App\Helpers;

use App\Http\Enum\WorkDiv;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Storage;

/**
 * Class JobBacklog
 * @package App\Helpers
 */
class JobBacklog implements Arrayable//, \Serializable
{
    /** @var array */
    private $arguments;

    /**
     * JobBacklog constructor.
     * @param array $arguments
     */
    public function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    /**
     * @param int $work_div
     * @param array $id_list
     * @return $this
     * @throws \ReflectionException
     */
    public function set(int $work_div, array $id_list): JobBacklog
    {
        if (in_array($work_div, WorkDiv::toArray())) {
            if (!isset($this->arguments[$work_div])) {
                $this->arguments[$work_div] = [];
            }

            $this->arguments[$work_div] = array_merge($this->arguments[$work_div], array_filter(array_map(function ($id) {
                $id = (int)$id;
                return $id ? $id : null;
            }, $id_list)));
        }

        return $this;
    }

    /**
     * @return array
     */
    public function divs(): array
    {
        return array_keys($this->arguments);
    }

    /**
     * @param int $work_div
     * @return array
     */
    public function idList(int $work_div): array
    {
        return isset($this->arguments[$work_div]) && is_array($this->arguments[$work_div]) ? $this->arguments[$work_div] : [];
    }

    public function toArray()
    {
        return $this->arguments;
    }

    /**
     * @return bool
     */
    public function publish(): bool
    {
        $out = json_decode(Storage::get('backlog.json'), true);

        foreach ($this->arguments as $work_div => $id_list) {
            $value = array_unique(array_merge($out[$work_div] ?? [], $id_list));
            $out[$work_div] = $value;
        }

        return Storage::put('backlog.json', json_encode($out));
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        $file_json = sprintf('backlogs/%s.1.json', date('Y-m-d_H:i:s'));
        $full_path_json = storage_path("app/$file_json");
        Storage::put($file_json, json_encode(array_merge(...array_values($this->arguments))));

        $file = sprintf('backlogs/%s.1.sh', date('Y-m-d_H:i:s'));
        $full_path = storage_path("app/$file");
        $artisan = base_path('artisan');
        $log = storage_path('logs/backlog.log');
        $value = addcslashes(json_encode($this->arguments), '"');
        $exec = <<<SH
value="$value"
echo "[`date +%Y-%m-%d\ %T`] `php $artisan backlog:work \"\$value\"` " >> $log
rm -f $full_path
rm -f $full_path_json
SH;
        return Storage::put($file, $exec);
    }
}
