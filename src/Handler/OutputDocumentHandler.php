<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Handler;

use Katmore\ErrorHandling\ {
    Event,
    Metadata,
    Payload
};

class OutputDocumentHandler extends EventHandler
{
    /**
     * @var bool
     */
    private $displayErrors;

    /**
     * @var bool
     */
    private $htmlFormat;

    /**
     * @var bool
     */
    private $httpHeaders;

    public function __construct(bool $displayErrors, bool $htmlFormat, bool $httpHeaders)
    {
        $this->displayErrors = $displayErrors;
        $this->htmlFormat = $htmlFormat;
        $this->httpHeaders = $httpHeaders;
    }

    /**
     * @return mixed
     */
    public function callback(Event $errorEvent)
    {
        if (!$this->httpHeaders && !headers_sent()) {
            http_response_code(500);
        }

        if (!$this->htmlFormat) {
            echo "<!--ERROR--><pre>\n";
        }

        if ($this->displayErrors) {
            echo "An error has occurred.\n";
        } else {
            echo "We are experiencing difficulties.\n";
            echo "Please contact support if this problem persists.\n";
        }

        $errorPayload = $errorEvent->getPayload();

        echo "Reference: {$errorEvent->getPayload()->getReference()}\n";

        if ($this->displayErrors) {
            iterator_apply($errorPayload, function (string $v, string $f): void {
                echo "   - $f: $v\n";
            });
            $backtrace = $errorPayload->getBacktrace();
            if (count($backtrace)) {
                echo "Backtrace:\n";
                iterator_apply($backtrace, function (Metadata\BacktraceNode $trace, int $level): void {
                    echo "   $level.\n";
                    array_filter($trace->toArray(), function ($v, string $f): void {
                        if (!is_scalar($v)) {
                            $v = json_encode($v, JSON_INVALID_UTF8_IGNORE);
                        }
                        echo "     - $f: $v\n";
                    }, ARRAY_FILTER_USE_BOTH);
                });
            }
        }

        if (!$this->htmlFormat) {
            echo "<!--ERROR--><pre>\n";
        }
    }
}
