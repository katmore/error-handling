<?php
namespace Katmore\ErrorHandling\Component;

interface ArraySerializableComponent extends  \JsonSerializable, \ArrayAccess, \Iterator {
    public function toArray(): array;
}