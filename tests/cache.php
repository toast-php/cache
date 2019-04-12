<?php

use Gentry\Gentry\Wrapper;

/** Testsuite for Toast\Cache\Cache */
return function () : Generator {
    $object = Wrapper::createObject(Toast\Cache\Cache::class);
    /** persist yields true */
    yield function () use ($object) {
        $result = $object->persist();
        assert(true);
    };

    /** getInstance yields true */
    yield function () use ($object) {
        $result = $object->getInstance();
        assert(true);
    };

    /** get yields true */
    yield function () use ($object) {
        $result = $object->get('blarps', 'MIXED');
        assert(true);
    };
    /** get yields true */
    yield function () use ($object) {
        $result = $object->get('blarps');
        assert(true);
    };

    /** getMultiple yields true */
    yield function () use ($object) {
        $result = $object->getMultiple(iterable, 'MIXED');
        assert(true);
    };
    /** getMultiple yields true */
    yield function () use ($object) {
        $result = $object->getMultiple(iterable);
        assert(true);
    };

    /** has yields true */
    yield function () use ($object) {
        $result = $object->has('blarps');
        assert(true);
    };

    /** clear yields true */
    yield function () use ($object) {
        $result = $object->clear();
        assert(true);
    };

    /** delete yields true */
    yield function () use ($object) {
        $result = $object->delete('blarps');
        assert(true);
    };

    /** deleteMultiple yields true */
    yield function () use ($object) {
        $result = $object->deleteMultiple(iterable);
        assert(true);
    };

    /** set yields true */
    yield function () use ($object) {
        $result = $object->set('blarps', 'MIXED', null|int|DateInterval);
        assert(true);
    };
    /** set yields true */
    yield function () use ($object) {
        $result = $object->set('blarps', 'MIXED');
        assert(true);
    };

    /** setMultiple yields true */
    yield function () use ($object) {
        $result = $object->setMultiple(null|int|DateInterval, 'MIXED');
        assert(true);
    };
    /** setMultiple yields true */
    yield function () use ($object) {
        $result = $object->setMultiple(null|int|DateInterval);
        assert(true);
    };

    /** setDeferred yields $result === true */
    yield function () use ($object) {
        $result = $object->setDeferred('blarps', 'MIXED');
        assert($result === true);
    };

    /** commit yields true */
    yield function () use ($object) {
        $result = $object->commit();
        assert(true);
    };

};
