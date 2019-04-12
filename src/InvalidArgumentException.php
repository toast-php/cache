<?php

namespace Toast\Cache;

use Psr\SimpleCache;

class InvalidArgumentException extends \InvalidArgumentException implements SimpleCache\InvalidArgumentException
{
}

