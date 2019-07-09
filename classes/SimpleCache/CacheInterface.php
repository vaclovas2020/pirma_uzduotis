<?php


namespace SimpleCache;


interface CacheInterface
{
    public function get(string $key, $default = null);

    public function set(string $key, $value, int $ttl = null): bool;

    public function delete(string $key): bool;

    public function clear(): bool;

    public function getMultiple(array $keys, $default = null): array;

    public function setMultiple(array $values, int $ttl = null): bool;

    public function deleteMultiple(array $keys): bool;

    public function has(string $key): bool;

}
