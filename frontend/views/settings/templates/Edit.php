<?php

namespace themes\clipone\views\ticketing\settings\templates;

use packages\ticketing\contracts\ITemplate;
use packages\ticketing\Template;
use packages\userpanel\views\Form;
use themes\clipone\Navigation;
use themes\clipone\views\FormTrait;
use themes\clipone\views\ticketing\HelperTrait;
use themes\clipone\views\ticketing\TemplateTrait;
use themes\clipone\ViewTrait;

/**
 * @phpstan-import-type SelectOptionType from HelperTrait
 */
class Edit extends Form
{
    use FormTrait;
    use ViewTrait;
    use HelperTrait;
    use TemplateTrait;

    public ?ITemplate $template = null;

    public function __beforeLoad(): void
    {
        $this->setTitle(t('titles.ticketing.templates.edit'));

        Navigation::active($this->getTicketingSettingsMenuItemName('ticketing_settings_templates'));
    }

    public function setTemplate(ITemplate $template): void
    {
        $this->template = $template;

        $this->setDataForm($template->toArray());
        $this->setDataForm($template->getDepartmentID() ?: '', 'department');
    }

    /**
     * @return SelectOptionType[]
     */
    public function getStatusesForSelect(): array
    {
        return [
            [
                'title' => t('titles.ticketing.templates.status.active'),
                'value' => Template::ACTIVE,
            ],
            [
                'title' => t('titles.ticketing.templates.status.deactive'),
                'value' => Template::DEACTIVE,
            ],
        ];
    }
}
