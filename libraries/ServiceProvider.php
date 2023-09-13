<?php

namespace packages\ticketing;

use packages\ticketing\contracts\IServiceProvider;
use packages\ticketing\contracts\ITemplateManager;

class ServiceProvider implements IServiceProvider
{
    private ?ITemplateManager $templateManager = null;

    public function getTemplateManager(): ITemplateManager
    {
        if (!$this->templateManager) {
            $this->templateManager = new TemplateManager($this);
        }

        return $this->templateManager;
    }
}