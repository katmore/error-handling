<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Metadata;

use Katmore\ErrorHandling\Exception;

class Backtrace implements \Countable, \SeekableIterator
{
    /**
     * @internal
     * @var BacktraceNode[] The BacktraceNode objects
     */
    protected $node = [];

    /**
     * @internal
     * @var int BacktraceNode iterator position boundary
     */
    protected $boundary = -1;

    /**
     * @internal
     * @var int The current BacktraceNode iterator position
     */
    protected $position = 0;

    /**
     * Create a Backtrace object using with BacktraceNode objects
     *
     * @return Backtrace
     *
     * @param BacktraceNode[] $backtraceNode
     *            The <i>BacktraceNode</i> objects
     *
     * @throws Exception\UnexpectedValueException if any backtrace node element is not a <i>BacktraceNode</i> instance
     */
    protected function withBacktraceNode(array $backtraceNode): Backtrace
    {
        array_walk($backtraceNode, function ($node, $key): void {
            if (!$node instanceof BacktraceNode) {
                throw new Exception\UnexpectedValueException("backtrace node element with key '$key' is not an instance of " . BacktraceNode::class);
            }
        });

        /**
         * @var Backtrace
         * @internal
         */
        $backtrace = clone $this;
        $backtrace->node = array_values($backtraceNode);
        $backtrace->position = 0;
        $backtrace->boundary = count($backtrace->node) - 1;
        return $backtrace;
    }

    /**
     * Get the number of BacktraceNode elements
     *
     * @return int The number of BacktraceNode elements
     *
     * @see \Countable::count()
     */
    public function count(): int
    {
        return $this->boundary + 1;
    }

    /**
     * Seek to a BacktraceNode iterator position
     *
     * @param int $position
     *            The BacktraceNode iterator position to seek to
     *
     * @see \SeekableIterator::seek()
     */
    public function seek($position): void
    {
        if ($position > $this->boundary) {
            throw new Exception\OutOfBoundsException("out of bounds seek: $position (boundary: {$this->boundary})");
        }
        $this->position = $position;
    }

    /**
     * Get the current BacktraceNode iterator element
     *
     * @return BacktraceNode|null Either the current <i>BacktraceNode</i> object or <i>null</i> if the current BacktraceNode index position is invalid
     *
     * @see \Iterator::current()
     */
    public function current(): ?BacktraceNode
    {
        return $this->position > $this->boundary ? $this->node[$this->position] : null;
    }

    /**
     * Get the current BacktraceNode iterator position
     *
     * @return int The current BacktraceNode iterator position
     *
     * @see \Iterator::key()
     */
    public function key(): int
    {
        return $this->position;
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
        return $this->position > $this->boundary;
    }
}
