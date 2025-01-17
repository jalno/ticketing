<?php

namespace themes\clipone\views\ticketing\settings\labels;

use packages\base\DB;
use packages\ticketing\contracts\ILabel;
use packages\userpanel\views\Form;
use themes\clipone\Navigation;
use themes\clipone\views\FormTrait;
use themes\clipone\views\ticketing\HelperTrait;
use themes\clipone\views\ticketing\LabelTrait;
use themes\clipone\ViewTrait;

class Delete extends Form
{
    use FormTrait;
    use ViewTrait;
    use HelperTrait;
    use LabelTrait;

    public ?ILabel $label = null;

    public function __beforeLoad(): void
    {
        $this->setTitle(t('titles.ticketing.labels.delete'));

        Navigation::active($this->getTicketingSettingsMenuItemName('ticketing_settings_labels'));
    }

    public function setLabel(ILabel $label): void
    {
        $this->label = $label;

        $this->setDataForm($label->toArray());
    }

    public function getTicketsCount(): int
    {
        $query = DB::where('label_id', $this->label->getID());
        return $query->getValue('ticketing_tickets_labels', 'COUNT(*)');
    }
}
