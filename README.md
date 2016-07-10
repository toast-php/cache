# Cache
Caching module for the Gentry test framework

Why would a testing framework need caching, you might ask? Well, sometimes a
particular piece of code does something that isn't that easily otherwise checked
(e.g. sending a mail), or if you write
[acceptance tests](http://gentry.monomelodies.nl/acceptance) you might need a
way to check if the external server did something correctly. The module is
called `cache` because it's fully PSR-6 compliant, but you really should think
of it more as a shared pool you can access for temporary storage while running
a test scenario.

## Installation

### Using composer (recommended)
```bash
composer require gentry/cache
```

### Manual
- Download or clone the repository somewhere;
- Register `/path/to/cache/src` for the `Gentry\Cache` namespace in your
  autoloader.

## Initialising a cache pool
Create a new `Pool` object like so:

```php
<?php

use Gentry\Cache\Pool;

$pool = new Pool;

```

Each pool by default identifies itself using the `GENTRY_CLIENT` environment
variable, which acts as a unique session ID of sorts.

Alternatively, the cache pool can also be accessed as a singleton:

```php
<?php

use Gentry\Cache\Pool;

$pool = Pool::getInstance();

```

This is escpecially handy when sharing throughout tests; although as long as the
`GENTRY_CLIENT` stays the same, the pool will reload existing data regardless.

## Storing and retrieving items in the cache
Each item stored must be wrapped in an object implementing
`Psr\Cache\CacheItemInterface`. Gentry supplies a `Gentry\Cache\Item` class for
this purpose (but feel free to use something compatible...):

```php
<?php

use Gentry\Cache\Pool;
use Gentry\Cache\Item;

$someVariable = 'I need to be cached!';

$cacheItem = new Item('some-unique-key', $someVariable);

$pool = Pool::getInstance();
$pool->save($cacheItem);
$pool->hasItem('some-unique-key'); // true

// ...somewhere else in your code...

$item = Pool::getInstance()->getItem('some-unique-key');
echo $item->get(); // string "I need to be cached!"

```

## Other operations
PSR-6 defines a bunch of other methods you can use. Not all of these have
relevant implementations since this simple cache is not meant to be used outside
of Gentry so not every method (e.g. `expiresAt`) will make sence. However, see
the API docs for full descriptions.

