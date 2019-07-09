<?php


namespace SimpleCache;


class FileCache implements CacheInterface
{
    private $defaultTtl;
    private $cachePath;

    public function __construct(string $cachePath = "cache", int $defaultTtl = 300)
    {
        $this->cachePath = $cachePath;
        $this->defaultTtl = $defaultTtl;
        if (!file_exists($this->cachePath) && file_exists(dirname($cachePath))){
            mkdir($cachePath);
        }
    }


    public function get(string $key, $default = null)
    {
        $path = $this->cachePath . DIRECTORY_SEPARATOR . $key;
        if (file_exists($path)) {
            $expiresAt = @filemtime($path);
            if ($expiresAt === false) {
                return $default;
            }
            if (time() >= $expiresAt) {
                @unlink($path);
                return $default;
            }
            return unserialize(file_get_contents($this->cachePath . DIRECTORY_SEPARATOR . $key));
        } else return $default;
    }

    public function set(string $key, $value, int $ttl = null): bool
    {
        $serializedValue = serialize($value);
        $path = $this->cachePath . DIRECTORY_SEPARATOR . $key;
        if ($ttl === null) {
            $ttl = $this->defaultTtl;
        }
        $expiresAt = time() + $ttl;
        if (@file_put_contents($path, $serializedValue) === false) {
            return false;
        }
        if (@touch($path, $expiresAt) === false) {
            @unlink($path);
            return false;
        }
        return true;
    }

    public function delete(string $key): bool
    {
        $path = $this->cachePath . DIRECTORY_SEPARATOR . $key;
        if (file_exists($path)) {
            return @unlink($path);
        } else return true;
    }

    public function clear(): bool
    {
        $resultArray = array_map('unlink', glob($this->cachePath . DIRECTORY_SEPARATOR . '*'));
        $success = true;
        foreach ($resultArray as $result) {
            if ($result === false) {
                $success = false;
                break;
            }
        }
        return $success;
    }

    public function getMultiple(array $keys, $default = null): array
    {
        $resultArray = array();
        foreach ($keys as $key) {
            $value = $this->get($key, $default);
            $resultArray[$key] = $value;
        }
        return $resultArray;
    }

    public function setMultiple(array $values, int $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            if ($this->set($key, $value, $ttl) === false) {
                return false;
            }
        }
        return true;
    }

    public function deleteMultiple(array $keys): bool
    {
        foreach ($keys as $key) {
            if ($this->delete($key) === false) {
                return false;
            }
        }
        return true;
    }

    public function has(string $key): bool
    {
        $path = $this->cachePath . DIRECTORY_SEPARATOR . $key;
        if (file_exists($path)) {
            $expiresAt = @filemtime($path);
            if ($expiresAt === false) {
                return false;
            }
            if (time() >= $expiresAt) {
                @unlink($path);
                return false;
            }
            return true;
        } else return false;
    }
}
