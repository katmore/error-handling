<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Component;

interface ArraySerializableComponent extends \JsonSerializable, \ArrayAccess, \Iterator
{
    public function toArray(): array;
}
