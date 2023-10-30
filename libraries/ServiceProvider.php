<?php

namespace packages\ticketing;

use packages\ticketing\contracts\ILabelManager;
use packages\ticketing\contracts\IServiceProvider;
use packages\ticketing\contracts\ITemplateManager;

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
