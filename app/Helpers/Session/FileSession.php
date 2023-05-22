<?php

namespace App\Helpers\Session;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

/**
 * Class FileSession
 * @package App\Helpers\Session
 */
class FileSession implements SessionInterface
{
    private const MAX_LIFE_TIME = 3600;

    private $path;
    private $sid;
    private $value;

    /**
     * FileSession constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (!file_exists(storage_path('session')) || !is_dir(storage_path('session'))) {
            throw new \Exception('session folder not exist');
        }

        $this->path = storage_path('session');
        $this->value = ['data' => []];
    }

    public function __destruct()
    {
        $this->save();
    }

    /**
     * @return bool
     */
    public function aliveSessionId(): bool
    {
        $serialize = $this->fileReadStream();
        return null !== $serialize && isset($serialize['expire']) && time() <= $serialize['expire'];
    }

    public function maxLifeTime(): int
    {
        return self::MAX_LIFE_TIME;
    }

    /**
     * @return string|null
     */
    public function sessionId(): ?string
    {
        return $this->sid;
    }

    /**
     * @param string|null $id
     * @return SessionInterface
     */
    public function start(string $id = null): SessionInterface
    {
        try {
            if (!$this->sid) {
                if (is_string($id)) {
                    $this->sid = $id;
                    $this->readSession();
                } else {
                    $this->init();
                }
            }
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }

        return $this;
    }

    public function save()
    {
        if (is_array($this->value) && count($this->value)) {
            $this->writeSession();
        }
    }

    /**
     * @return array
     */
    public function all(): array
    {
        if (!(bool)env('APP_DEBUG')) {
            return [];
        }

        return $this->value;
    }

    public function exists(string $key): bool
    {
        return isset($this->value['data'][$key]);
    }

    /**
     * @param string $key
     * @return bool
     */
    #[\JetBrains\PhpStorm\Pure]
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->value['data']);
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->value['data'][$key] ?? $default;
    }

    /**
     * @param string $key
     * @param null $value
     * @return SessionInterface
     */
    public function set(string $key, $value = null): SessionInterface
    {
        $this->value['data'][$key] = $value;
        $this->save();
        return $this;
    }

    public function pull(string $key, $default = null): mixed
    {
        $value = $this->value['data'][$key] ?? $default;
        $this->del($key);
        $this->save();
        return $value;
    }

    /**
     * @param array $key_values
     * @return SessionInterface
     */
    public function put(array $key_values): SessionInterface
    {
        $this->value['data'] = array_replace($this->value['data'], $key_values);
        return $this;
    }

    /**
     * @param string $key
     * @return SessionInterface
     */
    public function remove(string $key): SessionInterface
    {
        $this->del($key);
        return $this;
    }

    /**
     * @param array $keys
     * @return SessionInterface
     */
    public function forget(array $keys): SessionInterface
    {
        foreach ($keys as $key) {
            $this->del($key);
        }
        return $this;
    }

    /**
     * @return SessionInterface
     */
    public function flush(): SessionInterface
    {
        $this->init();
        return $this;
    }

    public function refresh(): SessionInterface
    {
        $this->readSession();

        if ($this->aliveSessionId()) {
            $this->writeSession();
        }

        return $this;
    }

    /**
     * Create session ID
     * @link https://php.net/manual/en/sessionidinterface.create-sid.php
     * @return string
     */
    #[\JetBrains\PhpStorm\Pure]
    public function create_sid(): string
    {
        $this->sid = uniqid(dechex(rand(0x100, 0xfff)), true);
        return $this->sid;
    }

    private function writeSession(): void
    {
        try {
            $this->value['expire'] = time() + self::MAX_LIFE_TIME;
            $s = serialize($this->value);
            $i = rand(strpos($s, '{'), strrpos($s, ';'));
            $i = strpos($s, ';', $i);
            $s = substr($s, 0, $i) . ';s:5:""' . substr($s, $i);

            if (!$this->path || !is_dir($this->path) || !$this->sid) {
                Log::error('The session is unavailable', ['url' => request()->url(), 'method' => request()->getMethod(), 'path' => $this->path, 'sid' => $this->sid]);
                return;
            }

            $file = $this->path .'/'. $this->sid;

            if (is_writable($file)) {
                $fp = fopen($file, 'wb');
                $this->fileWriteStream($fp, $s ?? '');
                fclose($fp);
            } elseif (!is_dir($file)) {
                $fp = fopen($file, 'wb');
                $this->fileWriteStream($fp, $s ?? '');
                fclose($fp);
                chmod($file, 0600);
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }

    /**
     * @return array|null
     */
    private function fileReadStream(): ?array
    {
        $file = $this->path .'/'. $this->sid;

        if (!file_exists($file) || !is_file($file) || !is_readable($file) || !filesize($file)) {
            return null;
        }

        try {
            $fp = fopen ( $file , 'rb' );
            $contents = fread($fp, filesize($file));
            fclose($fp);
            $serialize = unserialize(str_ireplace(';s:5:""', '', $contents ?? ''));

            if (false === $serialize || !is_array($serialize) || empty($serialize)) {
                return null;
            }

            return $serialize;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            return null;
        }
    }

    private function readSession()
    {
        $serialize = $this->fileReadStream();

        if ($this->aliveSessionId() && isset($serialize['data']) && time() <= $serialize['expire']) {
            $this->value['data'] = $serialize['data'];
        }
    }

    /**
     * @param $fp
     * @param $string
     * @return int
     */
    private function fileWriteStream($fp, $string): int
    {
        for ($written = 0; $written < strlen($string); $written += $count) {
            $count = fwrite($fp, substr($string, $written));
            if ($count === false) {
                return $written;
            }
        }
        return $written;
    }

    private function del($key)
    {
        if (is_array($this->value) && is_array($this->value['data'])) {
            unset($this->value['data'][$key]);
        }
    }

    private function init()
    {
        $this->value = ['expire' => time() + self::MAX_LIFE_TIME, 'data' => []];
    }
}
