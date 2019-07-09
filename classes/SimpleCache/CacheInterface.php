<?php


namespace SimpleCache;


interface CacheInterface
{
    public function get(string $key, $default = null): mixed;

    public function set(string $key, mixed $value, int $ttl = null): bool;

    public function delete($key): bool;

    public function clear(): bool;

    public function getMultiple(array $keys, $default = null): bool;

    public function setMultiple(array $values, int $ttl = null): bool;

    public function deleteMultiple(array $keys): bool;

    public function has($key);

}
