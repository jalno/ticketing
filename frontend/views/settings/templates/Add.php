<?php

namespace themes\clipone\views\ticketing\settings\templates;

use packages\ticketing\Ticket_message as Message;
use packages\userpanel\Authentication;
use packages\userpanel\views\Form;
use themes\clipone\Navigation;
use themes\clipone\views\FormTrait;
use themes\clipone\views\ticketing\HelperTrait;
use themes\clipone\views\ticketing\TemplateTrait;
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
