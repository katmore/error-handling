<?php
namespace Katmore\ErrorHandling\Handler;

use Katmore\ErrorHandling\Metadata;

abstract class ErrorHandler
{

    /**
     *
     * @var bool|null determines if error details should be displayed
     */
    private $displayErrors;

    /**
     *
     * @var bool|null determines if cli mode is enabled
     */
    private $cliMode;

    /**
     * Override whether cli mode is enabled
     *
     * Explicitly enable or disable cli mode rather than using
     * the value of the <b><code>PHP_SAPI</code></b> constant.
     *
     * @return void
     * @param bool $cliModeOverride
     *            If the value is <i>true</i>, cli mode will be ENABLED.
     *            If the value is <i>false</i>, cli mode will be DISABLED.
     *            If the value is <i>null</i> or no value is provided,
     *            the override is reset and the default behavior will be applied: the
     *            value of the <b><code>PHP_SAPI</code></b> constant determines
     *            if cli mode is enabled.
     *            
     * @see ErrorHandler::isCliMode()
     */
    final protected function overrideCliMode(bool $cliModeOverride = null): void
    {
        $this->cliMode = $cliModeOverride;
    }

    /**
     * Override whether error details should be displayed.
     *
     * Explicitly indicate if error details should be displayed
     * rather than using the return value of <b><code>ini_get('display_errors')</code></b>.
     *
     * @return void
     * @param bool $displayErrorsOverride
     *            If the value is <i>true</i>, error details SHOULD be displayed.
     *            If the value is <i>false</i>, error details SHOULD NOT be displayed.
     *            If the value is <i>null</i>, or if no value is provided,
     *            the override is reset and the default behavior will be applied:
     *            the return value of <b><code>ini_get('display_errors')</code></b>
     *            determines if error details should be displayed.
     *            
     * @see ErrorHandler::displayErrors()
     */
    final protected function overrideDisplayErrors(bool $displayErrorsOverride = null): void
    {
        $this->displayErrors = $displayErrorsOverride;
    }

    /**
     * Enable this error handler's callback
     */
    abstract protected function enableCallback(): void;

    /**
     * Disable this error handler's callback
     */
    abstract protected function disableCallback(): void;

    /**
     *
     * @see
     */
    public function isCliMode(): bool
    {
        return is_bool($this->cliMode) ? $this->cliMode : PHP_SAPI === 'cli';
    }

    public function displayErrors(): bool
    {
        return is_bool($this->displayErrors) ? $this->displayErrors : ! ! ini_get('display_errors');
    }

    /**
     * Apply flags related to cli mode
     *
     * The value of PHP_SAPI is used to determine whether cli mode is enabled, unless
     * one of the following flags is provided:
     * <ul>
     * <li><code>ErrorHandlerFlag::ALWAYS_CLI_MODE</code> - Cli mode is always enabled</li>
     * <li><code>ErrorHandlerFlag::NEVER_CLI_MODE</code> - Cli mode is never enabled</li>
     * </ul>
     *
     * @param int $flags
     *            bitwise disjunction of flags
     *            
     * @see ErrorHandlerFlag::ALWAYS_CLI_MODE
     * @see ErrorHandlerFlag::NEVER_CLI_MODE
     */
    protected function applyCliModeFlags(int $flags): void
    {
        if ($flags & ErrorHandlerFlag::ALWAYS_CLI_MODE) {
            $this->overrideCliMode(true);
        } else if ($flags & ErrorHandlerFlag::NEVER_CLI_MODE) {
            $this->overrideCliMode(false);
        } else {
            $this->overrideCliMode();
        }
    }

    /**
     * Apply flags related to enabling handler callback
     *
     * <ul>
     * <li><code>ErrorHandlerFlag::DETECT_CLI_MODE_SAPI</code> - <b>(default)</b> Check <code>PHP_SAPI</code> to determine if cli mode is enabled (e.g. <code>PHP_SAPI==='cli'</code>)</li>
     * <li><code>ErrorHandlerFlag::ENABLE_CALLBACK</code> - Cli mode is always enabled</li>
     * <li><code>ErrorHandlerFlag::NEVER_CLI_MODE</code> - Cli mode is never enabled</li>
     * </ul>
     *
     * @param int $flags
     *            bitwise disjunction of flags
     *            
     * @see ErrorHandlerFlag::ALWAYS_CLI_MODE
     * @see ErrorHandlerFlag::NEVER_CLI_MODE
     */
    protected function applyEnableCallbackFlags(int $flags): void
    {
        if ($flags & ErrorHandlerFlag::ENABLE_CALLBACK) {
            $this->enableCallback();
        } else {
            $this->disableCallback();
        }
    }

    protected function applyDisplayErrorsFlags(int $flags)
    {
        if ($flags & ErrorHandlerFlag::NEVER_DISPLAY_ERRORS) {
            $this->overrideDisplayErrors(false);
        } elseif ($flags & ErrorHandlerFlag::ALWAYS_DISPLAY_ERRORS) {
            $this->overrideDisplayErrors(true);
        } else {
            $this->overrideDisplayErrors();
        }
    }

    protected function outputErrorDocument(array $data, array $backtrace, string $ref): void
    {
        if (! $this->isCliMode() && ! headers_sent()) {
            http_response_code(500);
        }

        if (! $this->isCliMode()) {
            echo "<!--ERROR--><pre>\n";
        }

        if ($this->displayErrors()) {
            echo "An error has occurred.\n";
        } else {
            echo "We are experiencing difficulties.\n";
            echo "Please contact support if this problem persists.\n";
        }

        echo "Reference: $ref\n";

        if ($this->displayErrors()) {
            array_walk($data, function (string $v, string $f) {
                echo "   - $f: $v\n";
            });
            if (! empty($backtrace)) {
                echo "Backtrace:\n";
                array_walk($backtrace, function (Metadata\BacktraceNode $trace, int $level) {
                    echo "   $level.\n";
                    array_filter($trace->toArray(), function ($v, string $f) {
                        if (! is_scalar($v))
                            $v = json_encode($v, JSON_INVALID_UTF8_IGNORE);
                        echo "     - $f: $v\n";
                    }, ARRAY_FILTER_USE_BOTH);
                });
            }
        }

        if (! $this->isCliMode()) {
            echo "<!--ERROR--><pre>\n";
        }
    }

    /**
     *
     * @param string[] $logMessage
     */
    protected function sendErrorLog(array $logMessage): void
    {
        if ($this->isCliMode()) {
            return;
        }
        array_walk($logMessage, function (string $message) {
            error_log($message, 0);
        });
    }
}