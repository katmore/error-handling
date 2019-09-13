<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Metadata;

use Katmore\ErrorHandling;

class BacktraceFactory
{
    /**
     * @var array[]
     */
    protected $backtraceArray;

    public function setBacktraceArray(array $backtraceArray): self
    {
        $this->backtraceArray = $backtraceArray;
        
        return $this;
    }

    protected function filteredBacktraceArray(): array
    {
        if ($this->backtraceArray === null) {
            $this->backtraceArray = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }
        
        return array_values(array_filter($this->backtraceArray, function (array $nodeData) {
            if (isset($nodeData['class'])) {
                if (false !== strpos($nodeData['class'], ErrorHandling::class)) {
                    return false;
                }
            }
            if (!isset($nodeData['file']) && !is_string($nodeData['file'])) {
                return false;
            }
            if (!isset($nodeData['line']) && !is_int($nodeData['line'])) {
                return false;
            }
            return true;
        }));
    }

    public function createBacktrace(): Backtrace
    {
        $backtraceNode = (new class($this->filteredBacktraceArray()) extends BacktraceNode {
            /**
             * @var BacktraceNode[]
             */
            public $backtraceNode;

            public function __construct(array $backtraceArray)
            {
                $this->backtraceNode = array_map(function (array $nodeData) {
                    return $this->withNodeData($nodeData['file'], $nodeData['line'], $nodeData);
                }, $backtraceArray);
            }
        })->backtraceNode;

        $backtrace = (new class($backtraceNode) extends Backtrace {
            /**
             * @var Backtrace
             */
            public $backtrace;

            public function __construct(array $backtraceNode)
            {
                $this->backtrace = $this->withBacktraceNode($backtraceNode);
            }
        })->backtrace;

        return $backtrace;
    }
}
