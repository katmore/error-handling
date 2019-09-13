<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Handler;

use Katmore\ErrorHandling;

class PhpErrorHandler extends ErrorHandler
{
    protected function enableCallback(): void
    {
    }

    protected function disableCallback(): void
    {
    }

    /**
     * PHP error handling callback function
     *
     * @param int $errno
     *            error type
     * @param string $errmsg
     *            error message
     * @param string $errfile
     *            error file
     * @param int $errline
     *            error line
     *
     * @see set_error_handler()
     */
    public function callback(int $errno, string $errmsg, string $errfile, int $errline): void
    {
        $phpError = new ErrorHandling\Payload\PhpError($errno, $errmsg, $errfile, $errline);

        $logMessage = $this->createErrorLogMessage($phpError->toArray(), $phpError->getBacktrace(), $phpError->getDigest(), $phpError->getUid(), 'error');

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
}
