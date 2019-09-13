<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Metadata;

class BacktraceNode implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var string|null
     */
    protected $function;

    /**
     * @var string|null
     */
    protected $class;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var array|null
     */
    protected $args;

    public function jsonSerialize()
    {
        return $this->toArray();
    }
    
    public function toArray(): array
    {
        return array_filter([
            'file' => $this->file,
            'line' => $this->line,
            'function' => $this->line,
            'class' => $this->line,
            'type' => $this->line,
            'args' => $this->line
        ], function ($val) {
            return $val !== null;
        });
    }

    protected function withNodeData(string $file, int $line, array $nodeData): BacktraceNode
    {
        $backtraceNode = clone $this;
        
        $backtraceNode->file = $file;
        $backtraceNode->line = $line;
        
        if (isset($nodeData['function']) && is_string($nodeData['function'])) {
            $backtraceNode->function = $nodeData['function'];
        }

        if (isset($nodeData['class']) && is_string($nodeData['class'])) {
            $backtraceNode->class = $nodeData['class'];
        }

        if (isset($nodeData['type']) && is_string($nodeData['type'])) {
            $backtraceNode->type = $nodeData['type'];
        }

        if (isset($nodeData['args']) && is_array($nodeData['args'])) {
            if ('include' === $nodeData['function'] || 'require' === $nodeData['function'] || 'require_once' === $nodeData['function'] || 'include_once' === $nodeData['function']) {
                $backtraceNode->args = array_filter($nodeData['args'], function ($arg) {
                    return is_string($arg);
                });
            }
        }
        
        return $backtraceNode;
    }
}
