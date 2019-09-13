<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Handler;

use Katmore\ErrorHandling\ {
    Metadata,
    Payload
};

class PhpErrorHandler extends ErrorHandler
{
    /**
     * @var int
     */
    protected $severity = 0;

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var string|null
     */
    protected $file;

    /**
     * @var int|null
     */
    protected $line;

    /**
     * @var Metadata\Backtrace
     */
    protected $backtrace;

    /**
     * @return PhpErrorHandler for chaining
     */
    public function setErrorDocumentHandler(?callable $errorDocumentHandler): self
    {
        return parent::setErrorDocumentHandler($errorDocumentHandler);
    }
    
    /**
     * @return PhpErrorHandler for chaining
     */
    public function setErrorLogHandler(?callable $errorLogHandler): self
    {
        return parent::setErrorLogHandler($errorLogHandler);
    }

    /**
     * @return PhpErrorHandler for chaining
     */
    public function setBacktraceFactory(Metadata\BacktraceFactory $backtraceFactory): self
    {
        return parent::setBacktraceFactory($backtraceFactory);
    }
    
    /**
     * @return PhpErrorHandler for chaining
     */
    public function setSystemContext($systemContext): self
    {
        return parent::setSystemContext($systemContext);
    }

    
    //public function __construct(int $flags = ErrorHandlerFlag::USE_DISPLAY_ERRORS_INI | ErrorHandlerFlag::DETECT_SAPI | ErrorHandlerFlag::ENABLE_CALLBACK | ErrorHandlerFlag::SEND_ERROR_LOG)
    /**
     * @param Metadata\SystemContext $systemContext
     */
    public function __construct()
    {
    }

    public function setPhpErrorData(int $severity, string $message, ?string $file = null, ?int $line = null): self
    {
        $this->severity = $severity;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->backtrace = $this->getBacktraceFactory()->createBacktrace();

        return $this;
    }

    /**
     * PHP error handling callback function
     *
     * @param int $severity error type
     * @param string $message error message
     * @param string $file error file
     * @param int $line error line
     *
     * @see set_error_handler()
     */
    public function createPhpError(): Payload\PhpError
    {

        /**
         * @var Payload\PhpError $phpError
         */
        $phpError = (new class($this->backtrace, $this->severity, $this->message, $this->file, $this->line) extends Payload\PhpError {
            public $phpError;

            public function __construct(Metadata\Backtrace $backtrace, int $severity, string $message, ?string $file = null, ?int $line = null)
            {
                $this->phpError = $this->withBacktrace($backtrace);
                $this->phpError = $this->withErrorData($severity, $message, $file, $line);
            }
        })->phpError;

        $this->sendErrorLog($logMessage);

        if ($this->isCliMode()) {
            if ($phpError->isFatal()) {
                fwrite(STDERR, "PHP Fatal error\n");
                exit(1);
            }
            return;
        }

        if (!$phpError->isFatal()) {
            return;
        }
        
        $this->outputErrorDocument($phpError->toArray(), $phpError->getBacktrace(), $phpError->getReference());

        die();
    }

    protected function enableCallback(): void
    {
        set_error_handler(function (int $severity, string $message, ?string $file = null, ?int $line = null) {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            $this->getBacktraceFactory()->setBacktraceArray(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            $this->setPhpErrorData($severity, $message, $file, $line);
            $this->createPhpError();
        });
    }

    protected function disableCallback(): void
    {
    }
}
