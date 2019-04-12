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
     * @var string The client ID for this set of test runs.
     */
    private $client;

    /**
     * @var array Array of deferred cache items.
     */
    private $deferred = [];

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
     */
    public function __construct()
    {
        $this->client = getenv("GENTRY_CLIENT");
        self::$path = sys_get_temp_dir()."/{$this->client}.cache";
        self::$cache = [];
        $this->__wakeup();
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
     */
    public static function persist()
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
     */
    public function __wakeup()
    {
        if (file_exists(self::$path)) {
            self::$cache = unserialize(file_get_contents(self::$path));
        } else {
            self::persist();
        }
    }

    /**
     * Get a singleton instance of the pool.
     *
     * @return Toast\Cache\Pool
     */
    public static function getInstance()
    {
        static $pool;
        if (!isset($pool)) {
            $pool = new static;
        }
        return $pool;
    }

    /**
     * Get an item from the cache identified by $key.
     *
     * @param string $key The key to retrieve.
     * @param mixed $default Optional default.
     * @return mixed If found, whatever was in the cache.
     * @throws InvalidArgumentException if $key is not a valid value.
     */
    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }
        return self::$cache ?? $default;
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
     */
    public function has($key)
    {
        return isset(self::$cache[$key]);
    }

    /**
     * Clear the entire cache instance and persist this to disk.
     *
     * @return true
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
     * @return true
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
     * @param Psr\Cache\CacheInterface $item The cache item to save. Note
     *  that this does _not_ have to be an instance of Toast\Cache\; you
     *  can store anything compatible as long as it's serializable.
     * @return true
     */
    public function save(CacheInterface $item)
    {
        self::$cache[$item->getKey()] = $item;
        self::persist();
        return true;
    }

    /**
     * Mark an item to be saved at a later time.
     *
     * @param Psr\Cache\CacheInterface $item The cache item to queue.
     * @return true
     * @see Toast\Cache\Pool::save
     */
    public function saveDeferred(CacheInterface $item)
    {
        $this->deferred[] = $item;
        return true;
    }

    /**
     * Commit (persist) all deferred items.
     *
     * @return true
     */
    public function commit()
    {
        while ($item = array_shift($this->deferred)) {
            $this->save($item);
        }
        return true;
    }
}

