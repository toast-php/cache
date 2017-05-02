<?php

namespace Toast\Cache;

use Psr\Cache;

class InvalidArgumentException extends \InvalidArgumentException
implements Cache\InvalidArgumentException
{
}

