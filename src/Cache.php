<?php

namespace Toast\Cache;

use Psr\SimpleCache\CacheInterface;
use ErrorException;

/**
 * A simple cache for Toast.
 *
 * Unlike a "serious" cache implementation, this simply stores cached values in
 * a file in your system's temp-dir as a serialized string for the duration of
 * this run.
 */
class Cache implements CacheInterface
{
    /**
     * @var string Full pathname of the temporary storage file.
     */
    private static $path;

    /**
     * @var array Key/value hash of the current cache contents.
     */
    private static $cache;

    /**
     * Constructor. Sets up the pool instance and wakes it up if possible.
     *
     * @param string $path Path where to store the cache file.
     * @return void
     */
    public function __construct(string $path)
    {
        self::$path = $path;
        self::$cache = [];
        $this->__wakeup();
    }

    /**
     * Expose the cache file name.
     *
     * @return string
     */
    public function getPath() : string
    {
        return self::$path;
    }

    /**
     * Magic destructor. Persists the cached data back to file.
     */
    public function __destruct()
    {
        self::persist();
    }
    
    /**
     * Persist the cached data back to file.
     *
     * @return void
     */
    public static function persist() : void
    {
        file_put_contents(self::$path, serialize(self::$cache));
        try {
            chmod(self::$path, 0666);
        } catch (ErrorException $e) {
        }
    }

    /**
     * Magic wakeup method. Either reads the existing data from file, or else
     * persists it for the first time (so the cache file will exist).
     *
     * @return void
     */
    public function __wakeup() : void
    {
        if (file_exists(self::$path)) {
            self::$cache = unserialize(file_get_contents(self::$path));
        } else {
            self::persist();
        }
    }

    /**
     * Get a singleton instance of the cache.
     *
     * @param string $path
     * @return Toast\Cache\Cache
     * @see Toast\Cache\Cache::__construct
     */
    public static function getInstance(string $path) : Cache
    {
        static $caches = [];
        if (!isset($caches[$path])) {
            $caches[$path] = new static($path);
        }
        return $caches[$path];
    }

    /**
     * Get an item from the cache identified by $key.
     *
     * @param string $key The key to retrieve.
     * @param mixed $default Optional default.
     * @return mixed If found, whatever was in the cache, else null.
     * @throws InvalidArgumentException if $key is not a valid value.
     */
    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }
        return self::$cache[$key] ?? $default;
    }

    /**
     * Get an array of items identified by $keys.
     *
     * @param iterable $keys An iterable of key strings to get.
     * @param mixed $default Optional default.
     * @return array An array of Toast\Cache\ objects representing the
     *  found items. Any keys not found will be initialized to a `null` 
     *  (but not persisted yet).
     * @throws Toast\Cache\InvalidArgumentException if any of the keys is not a
     *  valid (string) value, or is $keys is not iterable.
     */
    public function getMultiple($keys, $default = null)
    {
        if (!(is_array($keys) || (is_object($keys) && $keys instanceof Traversable))) {
            throw new InvalidArgumentException('$keys must be an array of an instance of Traversable');
        }
        $return = [];
        foreach ($keys as $key) {
            $return[$key] = $this->get($key) ?? $default;
        }
        return $return;
    }

    /**
     * Check if the pool has an item under the given $key.
     *
     * @param string $key The key to check.
     * @return bool True if the item exists, else false.
     * @throws Toast\Cache\InvalidArgumentException if $key is not a string.
     */
    public function has($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }
        return isset(self::$cache[$key]);
    }

    /**
     * Clear the entire cache instance and persist this to disk.
     *
     * @return bool Always returns true.
     */
    public function clear()
    {
        self::$cache = [];
        self::persist();
        return true;
    }

    /**
     * Delete the given item from the cache.
     *
     * @param string $key The item to delete.
     * @return true
     */
    public function delete($key)
    {
        unset(self::$cache[$key]);
        return true;
    }

    /**
     * Delete an array of items in batch.
     *
     * @param iterable $keys The keys to deleted.
     * @return bool Always returns true.
     * @throws Toast\Cache\InvalidArgumentException
     */
    public function deleteMultiple($keys)
    {
        if (!(is_array($keys) || (is_object($keys) && $keys instanceof Traversable))) {
            throw new InvalidArgumentException('$keys must be an array of an instance of Traversable');
        }
        array_walk($keys, function ($key) {
            if (!is_string($key)) {
                throw new InvalidArgumentException('Each $key must be a string');
            }
            unset(self::$cache[$key]);
        });
        return true;
    }

    /**
     * Save an item to the cache, and immediately persist.
     *
     * @param string $key
     * @param mixed $value
     * @param null|int|DateInterval $ttl Not implemented for Toast.
     * @return bool Always returns true.
     * @throws Toast\Cache\InvalidArgumentException if $key is not a legal
     *  value.
     */
    public function set($key, $value, $ttl = null)
    {
        self::$cache[$key] = $value;
        self::persist();
        return true;
    }

    /**
     * Delete an array of items in batch.
     *
     * @param iterable $values A list of key => value pairs.
     * @param null|int|DateInterval $ttl Optional, not implemented in
     *  Toast\Cache.
     * @return bool Always returns true.
     * @throws Toast\Cache\InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null)
    {
        if (!(is_array($values) || (is_object($values) && $values instanceof Traversable))) {
            throw new InvalidArgumentException('$values must be an array of an instance of Traversable');
        }
        array_walk($values, function ($value, $key) {
            if (!is_string($key)) {
                throw new InvalidArgumentException('Each $key must be a string');
            }
            self::$cache[$key] = $value;
        });
        return true;
    }
}

