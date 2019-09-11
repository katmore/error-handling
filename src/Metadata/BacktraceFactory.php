<?php
namespace Katmore\ErrorHandling\Metadata;

use Katmore\ErrorHandling;

class BacktraceFactory
{

    /**
     *
     * @var array[]
     */
    protected $backtraceArray;

    public function setBacktraceArray(array $backtraceArray): void
    {
        $this->backtraceArray = $backtraceArray;
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
            if (! isset($nodeData['file']) && ! is_string($nodeData['file'])) {
                return false;
            }
            if (! isset($nodeData['line']) && ! is_int($nodeData['line'])) {
                return false;
            }
            return true;
        }));
    }

    public function createBacktrace(): Backtrace
    {
        
        $node = (new class($this->filteredBacktraceArray()) extends BacktraceNode {

            /**
             *
             * @var BacktraceNode[]
             */
            public $node;

            public function __construct(array $backtraceArray)
            {
                $this->node = array_map(function (array $nodeData) {
                    return $this->withNodeData($nodeData['file'], $nodeData['line'], $nodeData);
                }, $backtraceArray);
            }
        })->node;

        $backtrace = (new class($node) extends Backtrace {

            /**
             *
             * @var Backtrace
             */
            public $backtrace;

            public function __construct(array $node)
            {
                $this->backtrace = $this->withNode($node);
            }
        })->backtrace;

        return $backtrace;
    }
}