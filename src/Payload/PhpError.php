<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Payload;

use Katmore\ErrorHandling\Metadata;

class PhpError extends HandledError
{
    /**
     * @var string|null file of the PHP error
     */
    protected $file;

    /**
     * @var string|null line of the PHP error
     */
    protected $line;

    /**
     * @var string PHP error message
     */
    protected $message;

    /**
     * @var int severity of the PHP error
     */
    protected $severity;

    /**
     * @var Metadata\Backtrace
     */
    protected $backtrace;
    
    public function getTypeDesc(): string
    {
        return 'Php Error';
    }

    /**
     * Get the file of the PHP error
     *
     * @return string|null file of PHP error
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * Get the line of the PHP error
     *
     * @return string|null line of the PHP error
     */
    public function getLine(): ?int
    {
        return $this->line;
    }

    /**
     * Get the PHP error message
     *
     * @return string PHP error message
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the severity of the PHP error
     *
     * @return int severity of the PHP error
     */
    public function getSeverity(): int
    {
        return $this->severity;
    }

    /**
     * Get the PHP error backtrace
     *
     * @return Metadata\Backtrace PHP error backtrace
     */
    public function getBacktrace(): Metadata\Backtrace
    {
        return $this->backtrace;
    }

    public function toArray(): array
    {
        $phpError = [
            'message' => $this->message,
            'severity' => isset(Metadata\Severity::DESCRIPTION[$this->severity]) ? Metadata\Severity::DESCRIPTION[$this->severity] : 'UNKNOWN'
        ];

        if (!empty($this->file)) {
            $phpError['file'] = $this->file;
        }
        if (!empty($this->line)) {
            $phpError['line'] = $this->line;
        }

        return $phpError;
    }

    public function isFatal(): bool
    {
        return 0 !== (Metadata\Severity::FATAL & $this->severity);
    }

    protected function withBacktrace(Metadata\Backtrace $backtrace): PhpError
    {
        /**
         * @var PhpError
         * @internal
         */
        $phpError = clone $this;
        $phpError->backtrace = $backtrace;
        return $phpError;
    }

    protected function withErrorData(int $severity, string $message, ?string $file = null, ?int $line = null): PhpError
    {
        /**
         * @var PhpError
         * @internal
         */
        $phpError = clone $this;

        $phpError->uid = uniqid();
        $phpError->time = time();

        $phpError->severity = $severity;
        $phpError->message = $message;
        $phpError->file = $file;
        $phpError->line = $line;

        return $phpError;
    }
}
