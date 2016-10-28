# Example
As a quick example, let's see how we can use Gentry's cache to test something
that is supposed to send out emails.

## Step 1: intercept what you need
This is up to you and your implementation, but the idea is that whenever
something goes "outside" your application (like an email, but could also be a
call to or from an external API) you implement a _mock_ handler for that. Inside
the mock, instead of performing the intended action store the intended result
(or whatever value you're going to need to check on later) in the cache pool.
An example:

```php
<?php

function mockMailer($to, $message)
{
    // The real mailer would call `mail()` here:
    Gentry\Cache\Pool::getInstance()
        ->save(new Gentry\Cache\Item('mail', <<<EOT
To: $to
Message: $message
EOT
        ));
}
```

## Step 2: assert the cached data
Then, in your actual test, retrieve the cached item:

```php
<?php

// ...
$item = Gentry\Cache\Pool::getInstance()->getItem('mail');
yield assert($item->get() == <<<EOT
To: john@doe.com
Message: Howdy!
EOT
);
```

Note that per PSR-6, items are stored in the cache wrapped in an object
implementing `Psr\Cache\CacheInterface`. Use the `get` method to retrieve the
actual contents.

Also note that - Gentry's cache being extremely simple - anything you store will
be serialized/deserialized. So take care not to store stuff like database
handles (or implement proper `__sleep`/`__wakeup` methods if you must).

## Use unique keys
Keys are unique (as in, per Gentry run), so any duplicate keys will overwrite
existing values. This generally isn't a problem as the cache is reset before
each test is run, but it's good to keep in mind when choosing keys. Apart from
that, the key can be anything unique you can use in your test.

