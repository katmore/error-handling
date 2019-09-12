<?php
namespace Katmore\ErrorHandling\Handler;

class ErrorHandlerFlag
{

    /**
     *
     * @var int always display errors
     */
    const USE_DISPLAY_ERRORS_INI = 0;

    /**
     *
     * @var int detect cli mode from SAPI
     */
    const DETECT_CLI_MODE_SAPI = 0;

    /**
     *
     * @var int enable error handler callback
     */
    const ENABLE_CALLBACK = 0;

    /**
     *
     * @var int send errors to error log
     */
    const SEND_ERROR_LOG = 0;
    
    /**
     *
     * @var int disable error handler callback
     */
    const DISABLE_CALLBACK = 1;
    
    /**
     *
     * @var int DO NOT send errors to error log
     */
    const DONT_SEND_ERROR_LOG = 2;

    /**
     *
     * @var int override the value of
     */
    const ALWAYS_DISPLAY_ERRORS = 4;

    /**
     *
     * @var int never display errors
     */
    const NEVER_DISPLAY_ERRORS = 8;

    /**
     *
     * @var int always enable cli mode
     */
    const ALWAYS_CLI_MODE = 16;

    /**
     *
     * @var int never enable cli mode
     */
    const NEVER_CLI_MODE = 32;
}