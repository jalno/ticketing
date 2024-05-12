<?php

namespace packages\ticketing;

use packages\ticketing\Contracts\ILabelManager;
use packages\ticketing\Contracts\IServiceProvider;
use packages\ticketing\Contracts\ITemplateManager;

class ServiceProvider implements IServiceProvider
{
    private ?ITemplateManager $templateManager = null;
    private ?ILabelManager $labelManager = null;

    public function getTemplateManager(): ITemplateManager
    {
        if (!$this->templateManager) {
            $this->templateManager = new TemplateManager($this);
        }

        return $this->templateManager;
    }

    public function getLabelManager(): ILabelManager
    {
        if (!$this->labelManager) {
            $this->labelManager = new LabelManager($this);
        }

        return $this->labelManager;
    }
}
