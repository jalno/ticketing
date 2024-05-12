<?php

namespace themes\clipone\Views\Ticketing\Settings\Templates;

use packages\ticketing\Contracts\ITemplate;
use packages\ticketing\Template;
use packages\userpanel\Views\Form;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\Ticketing\HelperTrait;
use themes\clipone\Views\Ticketing\TemplateTrait;
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
