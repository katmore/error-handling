<?php
namespace Katmore\ErrorHandling\Metadata;

class Backtrace implements \Countable, \Iterator
{

    /**
     *
     * @var BacktraceNode[]
     */
    protected $node = [];

    /**
     *
     * @var int
     */
    protected $count = 0;

    /**
     *
     * @var int
     */
    protected $pos = 0;

    protected function withNode(array $node): Backtrace
    {
        $backtrace = clone $this;
        $backtrace->node = array_filter($node, function ($n) {
            return $n instanceof BacktraceNode;
        });
        $backtrace->pos = 0;
        $backtrace->count = count($backtrace->node);
        return $backtrace;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function current(): BacktraceNode
    {
        return $this->node[$this->pos];
    }

    public function key(): int
    {
        return $this->pos;
    }

    public function next()
    {
        $this->pos ++;
    }

    public function rewind()
    {
        $this->pos = 0;
    }

    public function valid(): bool
    {
        return isset($this->node[$this->pos]);
    }
}