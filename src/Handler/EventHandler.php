<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Handler;

use Katmore\ErrorHandling\ {
    Event
};

abstract class EventHandler
{
    /**
     * @return mixed
     */
    abstract public function callback(Event $errorEvent);
    
    /**
     * @return mixed
     */
    final public function __invoke(Event $errorEvent)
    {
        $this->callback($errorEvent);
    }
}
