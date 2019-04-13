# Cache
Caching module for the Toast test framework

Why would a testing framework need caching, you might ask? Well, sometimes a
particular piece of code does something that isn't that easily otherwise checked
(e.g. sending a mail), or if you write
[acceptance tests](http://toast.monomelodies.nl/acceptance) you might need a
way to check if the external server did something correctly. The module is
called `cache` because it's fully PSR-16 compliant, but you really should think
of it more as a shared pool you can access for temporary storage while running
a test scenario.

## Installation

### Using composer (recommended)
```bash
composer require toast/cache
```

### Manual
- Download or clone the repository somewhere;
- Register `/path/to/cache/src` for the `Toast\Cache` namespace in your
  autoloader.

## Initialising a cache pool
Create a new `Toast\Cache\Cache` object like so:

```php
<?php

use Toast\Cache\Cache;

$cache = new Cache('/path/to/storage');

```

Alternatively, the cache can also be accessed as a singleton:

```php
<?php

use Toast\Cache\Cache;

$pool = Cache::getInstance('/path/to/storage');

```

This is escpecially handy when sharing throughout tests; although as long as the
path stays the same, the cache will reload existing data regardless.

Note that you can instantiate multiple, sandboxed caches using different paths.

## Storing and retrieving items in the cache
Anything serializable can be stored in the cache using `set` or `setMultiple`:

```php
<?php

use Toast\Cache\Cache;

$someVariable = 'I need to be cached!';

$pool = Cache::getInstance('/path/to/storage');
$pool->save('some-unique-key', $someVariable);
$pool->has('some-unique-key'); // true

// ...somewhere else in your code...

$item = Cache::getInstance('/path/to/storage')->get('some-unique-key');
echo $item; // string "I need to be cached!"

```

## TTLs
PSR-16 allows cached items to define an optional Time To Live (TTL). Since this
does not make any sense in the context of unit testing, the parameter is
ignored.

