<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Component;

trait ArraySerializableComponentTrait
{
    private $arrayCacheAssoc;

    private $arrayCacheValue = [];

    private $arrayCacheKey = [];

    private $position = 0;

    abstract public function toArray(): array;

    public function __clone()
    {
        $this->resetArrayCache();
    }
    
    private function resetArrayCache(): void
    {
        $this->arrayCacheAssoc = null;
        $this->arrayCacheValue = [];
        $this->arrayCacheKey = [];
        $this->position = 0;
    }
    
    private function populateArrayCache(): void
    {
        if ($this->arrayCacheAssoc === null) {
            $this->arrayCacheAssoc = $this->toArray();
            $this->arrayCacheValue = array_values($this->arrayCacheAssoc);
            $this->arrayCacheKey = array_keys($this->arrayCacheAssoc);
        }
    }
    
    public function jsonSerialize()
    {
        $this->populateArrayCache();
        return $this->arrayCacheAssoc;
    }
    
    /**
     * Determine if serializable field exists
     *
     * @see \ArrayAccess::offsetExists()
     */
    public function offsetExists($field)
    {
        $this->populateArrayCache();
        return isset($this->arrayCacheAssoc[$field]);
    }
    
    /**
     * Get the value of a serializable field
     *
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($field)
    {
        $this->populateArrayCache();
        if (isset($this->arrayCacheAssoc[$field])) {
            return $this->arrayCacheAssoc[$field];
        }
    }
    
    /**
     * Get the current HandledError field value
     *
     * @return mixed The current HandledError field value
     *
     * @see \Iterator::current()
     */
    public function current()
    {
        $this->populateArrayCache();
        
        return isset($this->arrayValue[$this->position]) ? $this->arrayValue[$this->position] : null;
    }
    
    /**
     * Get the current HandledError field key
     *
     * @return int The current HandledError field key
     *
     * @see \Iterator::key()
     */
    public function key()
    {
        $this->populateArrayCache();
        
        return isset($this->arrayCacheKey[$this->position]) ? $this->arrayCacheKey[$this->position] : null;
    }
    
    /**
     * Advance the BacktraceNode iterator to the next position
     *
     * @see \Iterator::next()
     */
    public function next(): void
    {
        $this->position++;
    }
    
    /**
     * Reset the BacktraceNode iterator
     *
     * @see \Iterator::rewind()
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
    
    /**
     * Check if the current BacktraceNode iterator position is valid
     *
     * @return bool The value <i>true</i> if the current BacktraceNode iterator position is valid, <i>false</i> otherwise
     *
     * @see \Iterator::valid()
     */
    public function valid(): bool
    {
        $this->populateArrayCache();
        return isset($this->arrayValue[$this->position]);
    }
    
    
    
    /**
     * This method is ignored
     *
     * @see \ArrayAccess::offsetSet()
     */
    public function offsetUnset($offset): void
    {
    }
    
    /**
     * This method is ignored
     *
     * @see \ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value): void
    {
    }
}
