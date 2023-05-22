<?php

namespace App\Helpers\Utils;

use Illuminate\Http\Request as HttpRequest;

/**
 * Class ActivityHistory
 * @package App\Helpers
 *
 * @method ActivityHistory add(...$param)
 * @method ActivityHistory create(...$param)
 * @method ActivityHistory edit(...$param)
 * @method ActivityHistory update(...$param)
 * @method ActivityHistory delete(...$param)
 * @method ActivityHistory info(...$param)
 */
class ActivityHistory
{
    /** @var mixed */
    private static $instance = null;
    /** @var mixed */
    private $activities;
    /** @var mixed */
    private $fo;
    /** @var mixed */
    private $activity;
    private $content;
    private $user;
    private $note;

    /**
     * ActivityHistory constructor.
     * @param string $date
     * @param array|null $activities
     * @throws \Exception
     */
    public function __construct (string $date, array $activities = null)
    {
        $this->activities = ['add', 'create', 'edit', 'update', 'delete', 'info'];
        if ($activities) {
            $this->activities = array_merge($this->activities, array_diff(array_map('strtolower', $activities), $this->activities));
        }

        $filename = $this->createFilename($date);

        if ($filename) {
            $resource = fopen($filename,'a');
            if (!$resource) {
                throw new \Exception("cannot create or append file: $filename");
            }
            $this->fo = $resource;
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return ActivityHistory|null
     */
    #[\JetBrains\PhpStorm\NoReturn]
    public function __call ($name, $arguments): ?ActivityHistory
    {
        $name = strtolower($name);

        if ($this->fo && in_array($name, $this->activities, true)) {
            $this->activity = strtoupper($name);
            $this->content = empty($arguments) ? '[]' : $this->getContentData(1 === count($arguments) && is_array($arguments[0]) ? $arguments[0] : $arguments);
            return $this;
        }

        return null;
    }

    public function __destruct ()
    {
        if ($this->activity) {
            $this->append();
        }

        if ($this->fo) {
            fclose($this->fo);
            $this->fo = null;
        }
    }

    /**
     * @param $user
     * @return ActivityHistory
     */
    public function user($user): ActivityHistory
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param $note
     * @return ActivityHistory
     */
    public function note($note): ActivityHistory
    {
        $this->note = $note ? json_encode($note) : null;
        return $this;
    }

    /**
     * @return bool
     */
    public function append(): bool
    {
        if (!$this->activity) {
            return false;
        }

        $user = $this->user ?? $this->defaultUser();
        $result = fputs($this->fo, implode(',', array_map(static function ($cell) {
                return '"' . str_replace('"', '""', $cell) . '"';
            }, [date('H:i:s'), $user, app(HttpRequest::class)->url(), strtoupper($this->activity), $this->content, 'NOTE:' . $this->note]))."\n");
        $this->clear();
        return (bool)$result;
    }

    /**
     * @param array|null $activities
     * @return ActivityHistory
     * @throws \Exception
     */
    public static function singleton(array $activities = null): ActivityHistory
    {
        $key = date('Y-m-d');

        if (!isset(self::$instance[$key]) || !self::$instance[$key]) {
            self::$instance[$key] = new static($key, $activities);
        }

        return self::$instance[$key];
    }

    /**
     * @return mixed
     */
    private function defaultUser()
    {
        return auth()->getId() ?? '';
    }

    /**
     * @param array $input
     * @return string
     */
    private function getContentData(array $input): string
    {
        array_walk_recursive($input, function (&$item) {
            if (is_object($item) && method_exists($item, 'toArray')) {
                $item = $item->toArray();
            }
        });

        return json_encode($input);
    }

    private function clear()
    {
        $this->activity = null;
        $this->content = null;
        $this->user = null;
        $this->note = null;
    }

    /**
     * @param string $date
     * @return string
     */
    private function createFilename(string $date): string
    {
        $dir = storage_path('app');

        if (!file_exists($dir) || !is_dir($dir)) {
            mkdir($dir, 0775);
        }

        $dir = "$dir/public";

        if (!file_exists($dir) || !is_dir($dir)) {
            mkdir($dir, 0775);
        }

        return "$dir/log-action-$date.csv";
    }
}
