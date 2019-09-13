<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Handler;

class ErrorHandlerFlag
{
    /**
     * @var int always display errors
     */
    public const USE_DISPLAY_ERRORS_INI = 0;

    /**
     * @var int detect cli mode from SAPI
     */
    public const DETECT_SAPI = 0;

    /**
     * @var int enable error handler callback
     */
    public const ENABLE_CALLBACK = 0;

    /**
     * @var int send errors to error log
     */
    public const SEND_ERROR_LOG = 0;

    /**
     * @var int disable error handler callback
     */
    public const DISABLE_CALLBACK = 1;

    /**
     * @var int DO NOT send errors to error log
     */
    public const DONT_SEND_ERROR_LOG = 2;

    /**
     * @var int override the value of
     */
    public const ALWAYS_DISPLAY_ERRORS = 4;

    /**
     * @var int never display errors
     */
    public const NEVER_DISPLAY_ERRORS = 8;

    /**
     * @var int always enable cli mode
     */
    public const ALWAYS_CLI_MODE = 16;

    /**
     * @var int never enable cli mode
     */
    public const NEVER_CLI_MODE = 32;
}
