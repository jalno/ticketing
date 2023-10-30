<?php

namespace themes\clipone\views\ticketing\settings\labels;

use packages\ticketing\contracts\ILabel;
use packages\ticketing\Label;
use packages\userpanel\views\Form;
use themes\clipone\Navigation;
use themes\clipone\views\FormTrait;
use themes\clipone\views\ticketing\HelperTrait;
use themes\clipone\views\ticketing\LabelTrait;
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
