<?php
namespace Katmore\ErrorHandling\Payload;

use Katmore\ErrorHandling\Metadata;

class UncaughtException extends HandledError
{

    /**
     * @var \Throwable
     */
    protected $exception;
    
    /**
     *
     * @var Metadata\Backtrace
     */
    protected $backtrace;
    
    public function getException() : \Throwable {
        return $this->exception;
    }

    public function getBacktrace(): Metadata\Backtrace
    {
        return $this->backtrace;
    }

    public function toArray(): array
    {
        
        $uncaughtException = [
            'message' => $this->exception->getMessage(),
            'class' => $this->exception->getMessage(),
        ];
        
        if (!empty($code = $this->exception->getCode())) {
            $uncaughtException['code'] = $code;
        }
        
        if (!empty($file = $this->exception->getFile())) {
            $uncaughtException['file'] = $file;
        }
        
        if (!empty($line = $this->exception->getLine())) {
            $uncaughtException['line'] = $line;
        }
        
        return $uncaughtException;
        
    }
    
    protected function withBacktrace(Metadata\Backtrace $backtrace): UncaughtException
    {
        /**
         *
         * @var UncaughtException $uncaughtException
         * @internal
         */
        $uncaughtException = clone $this;
        $uncaughtException->backtrace = $backtrace;
        return $uncaughtException;
    }
    
    protected function withException(\Throwable $exception) : UncaughtException {
        
        /**
         *
         * @var UncaughtException $uncaughtException
         * @internal
         */
        $uncaughtException = clone $this;
        $uncaughtException->exception = $exception;
        
        return $uncaughtException;
    }
}