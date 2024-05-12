<?php

namespace themes\clipone\Views\Ticketing\Settings\Templates;

use packages\ticketing\Contracts\ITemplate;
use packages\userpanel\Views\Form;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\Ticketing\HelperTrait;
use themes\clipone\Views\Ticketing\TemplateTrait;
use themes\clipone\ViewTrait;

class Delete extends Form
{
    use FormTrait;
    use ViewTrait;
    use HelperTrait;
    use TemplateTrait;

    public ?ITemplate $template = null;

    public function __beforeLoad(): void
    {
        $this->setTitle(t('titles.ticketing.templates.delete'));

        Navigation::active($this->getTicketingSettingsMenuItemName('ticketing_settings_templates'));
    }

    public function setTemplate(ITemplate $template): void
    {
        $this->template = $template;

        $this->setDataForm($template->toArray());
    }
}
