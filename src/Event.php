<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling;

class Event
{
    /**
     * @var Payload\HandledError
     */
    protected $payload;
    
    /**
     * @var Metadata\SystemContext
     */
    protected $systemContext;
    
    public function getContext(): Metadata\SystemContext
    {
        return $this->ref;
    }
    
    public function getPayload(): Payload\HandledError
    {
        return $this->payload;
    }
    
    protected function withPayload(Payload\HandledError $errorPayload): self
    {
        $event = clone $this;
        $event->payload = $errorPayload;
        return $event;
    }
    
    protected function withContext(Metadata\SystemContext $systemContext): self
    {
        $event = clone $this;
        $event->systemContext = $systemContext;
        return $event;
    }
}
