<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Metadata;

use Katmore\ErrorHandling\ {
    Component
};

class SystemContext implements Component\ArraySerializableComponent
{
    use Component\ArraySerializableComponentTrait;
    
    /**
     * @var bool
     */
    protected $cliMode;
    
    /**
     * @var bool
     */
    protected $displayErrors;
    
    /**
     * @var array
     */
    protected $server;
    
    protected function withContextData(array $server, ?bool $cliMode, ?bool $displayErrors): self
    {
        /**
         * @var SystemContext $systemContext
         */
        $systemContext = clone $this;
        $systemContext->server = $server;
        $systemContext->cliMode = $cliMode;
        $systemContext->displayErrors = $displayErrors;
        
        return $systemContext;
    }
    
    public function getServer(): array
    {
        return $this->server;
    }
    
    public function isCliMode(): bool
    {
        return $this->cliMode;
    }
    
    public function displayErrors(): bool
    {
        return $this->displayErrors;
    }
    
    public function toArray(): array
    {
        return [
            'cliMode' => $this->cliMode,
            'displayErrors' => $this->displayErrors,
        ];
    }
}
