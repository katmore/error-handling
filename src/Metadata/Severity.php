<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Metadata;

interface Severity
{
    /**
     * @var int Bitwise disjunction of fatal PHP error types
     *
     * @see E_ERROR
     * @see E_USER_ERROR
     * @see E_PARSE
     * @see E_CORE_ERROR
     * @see E_COMPILE_ERROR
     * @see E_RECOVERABLE_ERROR
     */
    public const FATAL = E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR;
    
    /**
     * Hashmap of severity descriptions
     *
     * @var string[] each element key is the error type, and the element value is the severity description
     *
     *  @see E_ERROR
     *  @see E_WARNING
     *  @see E_PARSE
     *  @see E_NOTICE
     *  @see E_CORE_ERROR
     *  @see E_CORE_WARNING
     *  @see E_COMPILE_ERROR
     *  @see E_COMPILE_WARNING
     *  @see E_USER_ERROR
     *  @see E_USER_WARNING
     *  @see E_USER_NOTICE
     *  @see E_STRICT
     *  @see E_RECOVERABLE_ERROR
     *  @see E_DEPRECATED
     *  @see E_USER_DEPRECATED
     */
    public const DESCRIPTION = [
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED'
    ];
}
