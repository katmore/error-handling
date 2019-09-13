<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Handler;

use Katmore\ErrorHandling\ {
    Event,
    Metadata,
    Payload
};

abstract class ErrorHandler
{
    /**
     * @var Metadata\BacktraceFactory
     */
    private $backtraceFactory;

    /**
     * @var callable
     */
    private $errorDocumentHandler;
    
    /**
     * @var callable
     */
    private $errorLogHandler;
    
    /**
     * @var Metadata\SystemContext
     */
    private $systemContext;

    /**
     * Enable this error handler's callback
     * @internal
     */
    abstract protected function enableCallback(): void;

    /**
     * Disable this error handler's callback
     * @internal
     */
    abstract protected function disableCallback(): void;

    /**
     * Set the SystemContext object
     *
     * @param Metadata\SystemContext $systemContext The SystemContext object
     *
     * @return ErrorHandler for chaining
     */
    public function setSystemContext(Metadata\SystemContext $systemContext)
    {
        $this->systemContext = $this->systemContext;
        return $this;
    }
    
    /**
     * Set the BacktraceFactory
     *
     * @param Metadata\BacktraceFactory $backtraceFactory The BacktraceFactory
     *
     * @return ErrorHandler for chaining
     */
    public function setBacktraceFactory(Metadata\BacktraceFactory $backtraceFactory)
    {
        $this->backtraceFactory = $backtraceFactory;
        return $this;
    }

    /**
     * Set the error document handler
     *
     * @param callable $errorDocumentHandler The error document handler
     *
     * @return ErrorHandler for chaining
     */
    public function setErrorDocumentHandler(callable $errorDocumentHandler)
    {
        $this->errorDocumentHandler = $errorDocumentHandler;
        return $this;
    }
    
    /**
     * Set the error log handler
     *
     * @param callable $errorLogHandler The error log handler
     *
     * @return ErrorHandler for chaining
     */
    public function setErrorLogHandler(callable $errorLogHandler)
    {
        $this->errorLogHandler = $errorLogHandler;
        return $this;
    }

    /**
     * Get the error document handler
     *
     * @return callable The error document handler
     * @internal
     */
    final protected function getErrorDocumentHandler(): callable
    {
        return $this->errorDocumentHandler;
    }

    /**
     * Get the BacktraceFactory object
     *
     * @return Metadata\BacktraceFactory The BacktraceFactory object
     * @internal
     */
    final protected function getBacktraceFactory(): Metadata\BacktraceFactory
    {
        return $this->backtraceFactory;
    }

    /**
     * Apply flags related to error callbacks
     *
     * <ul>
     *
     * <li><code>ErrorHandlerFlag::ALWAYS_CLI_MODE</code> - Cli mode is always enabled</li>
     * <li><code>ErrorHandlerFlag::NEVER_CLI_MODE</code> - Cli mode is never enabled</li>
     * </ul>
     *
     * @internal
     *
     * @param int $flags bitwise disjunction of flags
     *
     * @see ErrorHandlerFlag::ALWAYS_CLI_MODE
     * @see ErrorHandlerFlag::NEVER_CLI_MODE
     */
    protected function applyCallbackFlags(int $flags): void
    {
        if ($flags & ErrorHandlerFlag::DISABLE_CALLBACK) {
            $this->disableCallback();
        } else {
            $this->enableCallback();
        }
    }
    
    protected function createEvent(Payload\HandledError $errorPayload): Event
    {
        return (new class() extends Event {
            public $event;
            public function __construct(Payload\HandledError $errorPayload, Metadata\SystemContext $systemContext)
            {
                $this->event = $this->withPayload($errorPayload);
                $this->event = $this->withContext($systemContext);
            }
        })->event;
    }
    
    protected function callErrorDocumentHandler(Event $errorEvent): void
    {
        if (is_callable($this->errorDocumentHandler)) {
            $this->errorDocumentHandler($errorEvent);
        }
        $this->defaultErrorDocumentHandler($errorEvent);
    }
    
    protected function callErrorLogHandler(Event $errorEvent): void
    {
        if (is_callable($this->errorLogHandler)) {
            $this->errorDocumentHandler($errorEvent);
        }
    }
}
