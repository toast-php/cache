<?php

namespace Gentry\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use ErrorException;

/**
 * A simple cache pool for Gentry.
 *
 * Unlike a "serious" cache implementation, this simply stores cached values in
 * a file in your system's temp-dir as a serialized string for the duration of
 * this run.
 */
class Pool implements CacheItemPoolInterface
{
    /**
     * @var string The client ID for this set of test runs.
     */
    private $client;

    /**
     * @var array Psr\Cache\CacheItemInterface Array of deferred cache items.
     */
    private $deferred = [];

    /**
     * @var string Full pathname of the temporary storage file.
     */
    private static $path;

    /**
     * @var array Psr\Cache\CacheItemInterface Key/value hash of the current
     *  cache contents.
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
     * @return Gentry\Cache\Pool
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
     * @return mixed If found, whatever was in the cache.
     * @throws InvalidArgumentException if no such key exists.
     */
    public function getItem($key)
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        throw new InvalidArgumentException($key);
    }

    /**
     * Get an array of items identified by $keys.
     *
     * @param array $keys An optional array of key strings to get. If omitted
     *  or empty an empty array is returned.
     * @return array An array of Gentry\Cache\Item objects representing the
     *  found items. Any keys not found will be initialized to a `null` Item
     *  (but not persisted yet).
     */
    public function getItems(array $keys = [])
    {
        if (!$keys) {
            return [];
        }
        $return = [];
        foreach ($keys as $key) {
            try {
                $return[] = $this->getItem($key);
            } catch (InvalidArgumentException $e) {
                $return[] = new Item($key, null);
            }
        }
        return $return;
    }

    /**
     * Check if the pool has an item under the given $key.
     *
     * @param string $key The key to check.
     * @return bool True if the item exists, else false.
     */
    public function hasItem($key)
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
    public function deleteItem($key)
    {
        unset(self::$cache[$key]);
        return true;
    }

    /**
     * Delete an array of items in batch.
     *
     * @param array $keys An array of keys to deleted.
     * @return true
     */
    public function deleteItems(array $keys)
    {
        array_walk($keys, function ($key) {
            unset(self::$cache[$key]);
        });
        return true;
    }

    /**
     * Save an item to the cache, and immediately persist.
     *
     * @param Psr\Cache\CacheItemInterface $item The cache item to save. Note
     *  that this does _not_ have to be an instance of Gentry\Cache\Item; you
     *  can store anything compatible as long as it's serializable.
     * @return true
     */
    public function save(CacheItemInterface $item)
    {
        self::$cache[$item->getKey()] = $item;
        self::persist();
        return true;
    }

    /**
     * Mark an item to be saved at a later time.
     *
     * @param Psr\Cache\CacheItemInterface $item The cache item to queue.
     * @return true
     * @see Gentry\Cache\Pool::save
     */
    public function saveDeferred(CacheItemInterface $item)
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

