<?php

use Gentry\Gentry\Wrapper;

/** Testsuite for Toast\Cache\Cache */
return function ($test) : Generator {
    $cache = Wrapper::createObject(Toast\Cache\Cache::class, sys_get_temp_dir().'/toast.cache');
    $test->beforeEach(function () use ($cache) {
        $cache->clear();
    });

    /** When storing an item using `set`, `persist` stores it in a temporary file and we can `get` it again */
    yield function () use ($cache) {
        $cache->set('test', new stdClass);
        $cache->persist();
        $stored = $cache->get('test');
        assert($stored instanceof stdClass);
        unlink(sys_get_temp_dir().'/toast.cache');
    };

    /** `getInstance` allows us to statically retrieve a cache as a singleton */
    yield function () {
        $cache = Toast\Cache\Cache::getInstance(sys_get_temp_dir().'/toast.cache');
        assert($cache instanceof Toast\Cache\Cache);
        assert($cache->getPath() === sys_get_temp_dir().'/toast.cache');
    };

    /** When calling with a default value, if the cached item is not found we get the default instead */
    yield function () use ($cache) {
        $test = $cache->get('non-existing', 'success');
        assert($test === 'success');
    };

    /** `setMultiple` allows us to store multiple cache items, and `getMultiple` allows us to retrieve them */
    yield function () use ($cache) {
        $result = $cache->setMultiple(['a' => new stdClass, 'b' => new stdClass]);
        assert($result === true);
        $stored = $cache->getMultiple(['a', 'b']);
        assert($stored['a'] instanceof stdClass);
        assert($stored['b'] instanceof stdClass);
    };

    /** has yields true if an item exists in the cache, else false */
    yield function () use ($cache) {
        $cache->set('foo', new stdClass);
        assert($cache->has('foo') === true);
        assert($cache->has('bar') === false);
    };

    /** clear yields true and clears the cache */
    yield function () use ($cache) {
        $cache->set('test', new stdClass);
        $result = $cache->clear();
        assert($result === true);
        assert($cache->has('test') === false);
    };

    /** delete yields true and removes an item from the cache */
    yield function () use ($cache) {
        $cache->set('test', new stdClass);
        $result = $cache->delete('test');
        assert($result === true);
        assert($cache->has('test') === false);
    };

    /** deleteMultiple yields true and removes multiple items from the cache */
    yield function () use ($cache) {
        $cache->set('foo', new stdClass);
        $cache->set('bar', new stdClass);
        $result = $cache->deleteMultiple(['foo', 'bar']);
        assert($cache->has('foo') === false);
        assert($cache->has('bar') === false);
    };
};

