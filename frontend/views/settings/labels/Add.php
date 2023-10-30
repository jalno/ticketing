<?php

namespace themes\clipone\views\ticketing\settings\labels;

use packages\ticketing\Ticket_message as Message;
use packages\userpanel\Authentication;
use packages\userpanel\views\Form;
use themes\clipone\Navigation;
use themes\clipone\views\FormTrait;
use themes\clipone\views\ticketing\HelperTrait;
use themes\clipone\views\ticketing\LabelTrait;
use themes\clipone\ViewTrait;

class Add extends Form
{
    use FormTrait;
    use ViewTrait;
    use HelperTrait;
    use LabelTrait;

    public function __beforeLoad(): void
    {
        $this->setTitle(t('titles.ticketing.labels.add'));
        $this->initFormData();

        Navigation::active($this->getTicketingSettingsMenuItemName('ticketing_settings_labels'));
    }

    public function initFormData(): void
    {
        if (!$this->getDataForm('color')) {
            $this->setDataForm('#337ab7', 'color');
        }
    }
}
