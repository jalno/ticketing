<?php

namespace themes\clipone\Views\Ticketing\Settings\Labels;

use packages\ticketing\TicketMessage as Message;
use packages\userpanel\Authentication;
use packages\userpanel\Views\Form;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\Ticketing\HelperTrait;
use themes\clipone\Views\Ticketing\LabelTrait;
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
