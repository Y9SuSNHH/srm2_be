<?php


namespace App\Helpers\Session;


interface SessionInterface extends \SessionIdInterface
{
    /**
     * Check alive session id
     *
     * @return bool
     */
    public function aliveSessionId():bool;

    /**
     * @return int
     */
    public function maxLifeTime(): int;

    /**
     * Get the current session ID.
     *
     * @return string|null
     */
    public function sessionId(): ?string;

    /**
     * Start the session, reading the data from a handler.
     *
     * @param string|null $id
     * @return SessionInterface
     */
    public function start(string $id = null): SessionInterface;

    /**
     * Save the session data to storage.
     *
     * @return void
     */
    public function save();

    /**
     * Get all of the session data, debug mode only
     *
     * @return array
     */
    public function all(): array;

    /**
     * Checks if a key exists.
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool;

    /**
     * Checks if a key is present and not null.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get an item from the session.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null): mixed;

    /**
     * Set a key / value pair in the session.
     *
     * @param string $key
     * @param mixed $value
     * @return SessionInterface
     */
    public function set(string $key, $value = null): SessionInterface;

    /**
     * Get the value of a given key and then forget it.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function pull(string $key, $default = null): mixed;

    /**
     * Set array of key / value pairs in the session.
     *
     * @param array $key_values
     * @return SessionInterface
     */
    public function put(array $key_values): SessionInterface;

    /**
     * Remove an item from the session, returning its value.
     *
     * @param string $key
     * @return SessionInterface
     */
    public function remove(string $key): SessionInterface;

    /**
     * Remove one or many items from the session.
     *
     * @param array $keys
     * @return SessionInterface
     */
    public function forget(array $keys): SessionInterface;

    /**
     * Remove all of the items from the session.
     *
     * @return SessionInterface
     */
    public function flush(): SessionInterface;

    /**
     * @return SessionInterface
     */
    public function refresh(): SessionInterface;
}
