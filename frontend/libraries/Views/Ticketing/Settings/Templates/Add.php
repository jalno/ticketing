<?php

namespace themes\clipone\Views\Ticketing\Settings\Templates;

use packages\ticketing\TicketMessage as Message;
use packages\userpanel\Authentication;
use packages\userpanel\Views\Form;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\Ticketing\HelperTrait;
use themes\clipone\Views\Ticketing\TemplateTrait;
use themes\clipone\ViewTrait;

class Add extends Form
{
    use FormTrait;
    use ViewTrait;
    use HelperTrait;
    use TemplateTrait;

    public function __beforeLoad(): void
    {
        $this->setTitle(t('titles.ticketing.templates.add'));
        $this->initFormData();

        Navigation::active($this->getTicketingSettingsMenuItemName('ticketing_settings_templates'));
    }

    public function initFormData(): void
    {
        if (!$this->getDataForm('message_format')) {
            $this->setDataForm(Authentication::getUser()->option('ticketing_editor') ?: Message::html, 'message_format');
        }
    }
}
