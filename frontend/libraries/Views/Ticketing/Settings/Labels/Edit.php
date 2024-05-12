<?php

namespace themes\clipone\Views\Ticketing\Settings\Labels;

use packages\ticketing\Contracts\ILabel;
use packages\userpanel\Views\Form;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\Ticketing\HelperTrait;
use themes\clipone\Views\Ticketing\LabelTrait;
use themes\clipone\ViewTrait;

/**
 * @phpstan-import-type SelectOptionType from HelperTrait
 */
class Edit extends Form
{
    use FormTrait;
    use ViewTrait;
    use HelperTrait;
    use LabelTrait;

    public ?ILabel $label = null;

    public function __beforeLoad(): void
    {
        $this->setTitle(t('titles.ticketing.labels.edit'));

        Navigation::active($this->getTicketingSettingsMenuItemName('ticketing_settings_labels'));
    }

    public function setLabel(ILabel $label): void
    {
        $this->label = $label;

        $this->setDataForm($label->toArray());
    }
}
