<?php

namespace Toast\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * Class representing an item in the cache.
 */
class Item implements CacheItemInterface
{
    /**
     * Constructor. Pass the desired key and value.
     *
     * @param string $key
     * @param mixed $value This _must_ be serializable.
     */
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /** 
     * @var string This item's unique key.
     */
    private $key;

    /**
     * @var mixed This item's value. Can be anything *as long as it satisties
     *  is_serializable*.
     */
    private $value;

    /**
     * Returns the key this item was registered under.
     *
     * @return string The key.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get the underlying value of the item.
     *
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * In PSR-6, this is supposed to check whether or not the item could be
     * retrieved; however, in Toast's simple implementation by this time we
     * _know_ that that succeeded.
     *
     * @return true
     */
    public function isHit()
    {
        return true; // for now
    }

    public function set($value)
    {
        $this->value = $value;
    }

    /**
     * Not implemented for this cache interface.
     *
     * @param DateTimeInterface $expiration
     * @return static The called object.
     */
    public function expiresAt($expiration)
    {
        return $this;
    }

    /**
     * Not implemented for this cache interface.
     *
     * @param int|DateInterval $time
     * @return static The called object.
     */
    public function expiresAfter($time)
    {
        return $this;
    }
}

