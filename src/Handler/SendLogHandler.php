<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Handler;

use Katmore\ErrorHandling\ {
    Event,
    Metadata,
    Payload
};

class SendLogHandler extends EventHandler
{
    protected function errorLog(string $message, string $prefix): void
    {
        error_log("$prefix: $message", 0);
    }

    /**
     * @return mixed
     */
    public function callback(Event $errorEvent)
    {

        /**
         * @var Payload\HandledError $errorPayload
         */
        $errorPayload = $errorEvent->getPayload();
        $prefix = $errorPayload->getReference();
        $this->errorLog($errorPayload->getTypeDesc(), $prefix);

        iterator_apply($errorPayload, function (string $v, string $f) use ($prefix): void {
            $this->errorLog("$f: $v", $prefix);
        });
        $backtrace = $errorPayload->getBacktrace();
        if (count($backtrace)) {
            iterator_apply($backtrace, function (Metadata\BacktraceNode $trace, int $level) use ($prefix): void {
                iterator_apply($trace, function ($v, string $f) use ($prefix): void {
                    if (!is_scalar($v)) {
                        $v = json_encode($v, JSON_INVALID_UTF8_IGNORE);
                    }
                    $this->errorLog("$f: $v", $prefix);
                });
            });
        }
    }
}
