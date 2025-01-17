<?php

namespace themes\clipone\views\ticketing\settings\templates;

use packages\base\views\traits\Form;
use packages\ticketing\Authorization;
use packages\ticketing\Department;
use packages\ticketing\Template;
use function packages\userpanel\url;
use packages\userpanel\views\Listview;
use themes\clipone\Navigation;
use themes\clipone\navigation\MenuItem;
use themes\clipone\views\FormTrait;
use themes\clipone\views\ListTrait;
use themes\clipone\views\ticketing\HelperTrait;
use themes\clipone\ViewTrait;

/**
 * @phpstan-import-type SelectOptionType from HelperTrait
 */
class Search extends Listview
{
    use Form;
    use FormTrait;
    use ListTrait;
    use ViewTrait;
    use HelperTrait;

    public static function onSourceLoad(): void
    {
        if (Authorization::is_accessed('settings_templates_search')) {
            $templates = new MenuItem('ticketing_settings_templates');
            $templates->setTitle(t('titles.ticketing.templates'));
            $templates->setURL(url('settings/ticketing/templates'));
            $templates->setIcon('fa fa-file-text-o');

            self::getTicketingSettingsMenu()->addItem($templates);
        }
    }

    public bool $canAdd = false;
    public bool $canEdit = false;
    public bool $canDelete = false;

    public function __construct(...$args)
    {
        parent::__construct(...$args);

        $this->canAdd = Authorization::is_accessed('settings_templates_add');
        $this->canEdit = Authorization::is_accessed('settings_templates_edit');
        $this->canDelete = Authorization::is_accessed('settings_templates_delete');
    }

    public function __beforeLoad(): void
    {
        $this->setTitle(t('titles.ticketing.templates'));
        $this->addBodyClass('ticketing-templates');
        $this->setButtons();

        Navigation::active($this->getTicketingSettingsMenuItemName('ticketing_settings_templates'));
    }

    public function setButtons(): void
    {
        $this->setButton('edit', $this->canEdit, [
            'title' => t('ticketing.edit'),
            'icon' => 'fa fa-edit',
            'classes' => ['btn', 'btn-xs', 'btn-teal'],
        ]);

        $this->setButton('delete', $this->canDelete, [
            'title' => t('ticketing.delete'),
            'icon' => 'fa fa-times',
            'classes' => ['btn', 'btn-xs', 'btn-bricky'],
        ]);
    }

    /**
     * @return SelectOptionType[]
     */
    public function getMessageTypesForSelect(): array
    {
        return [
            [
                'title' => t('choose'),
                'value' => '',
            ],
            [
                'title' => t('titles.ticketing.templates.message_type.both'),
                'value' => Template::ADD.','.Template::REPLY,
            ],
            [
                'title' => t('titles.ticketing.templates.message_type.add'),
                'value' => Template::ADD,
            ],
            [
                'title' => t('titles.ticketing.templates.message_type.reply'),
                'value' => Template::REPLY,
            ],
        ];
    }

    /**
     * @return SelectOptionType[]
     */
    public function getStatusesForSelect(): array
    {
        return [
            [
                'title' => t('choose'),
                'value' => '',
            ],
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

    /**
     * @return SelectOptionType[]
     */
    public function getDepartmentsForSelect(): array
    {
        $options = [
            [
                'title' => t('choose'),
                'value' => '',
            ],
        ];

        $query = new Department();
        $query->where('status', Department::ACTIVE);

        $departments = $query->get();

        foreach ($departments as $department) {
            $options[] = [
                'title' => $department->title,
                'value' => $department->id,
            ];
        }

        return $options;
    }

    /**
     * @return SelectOptionType[]
     */
    public function getComparisonsForSelect(): array
    {
        return [
            [
                'title' => t('search.comparison.contains'),
                'value' => 'contains',
            ],
            [
                'title' => t('search.comparison.equals'),
                'value' => 'equals',
            ],
            [
                'title' => t('search.comparison.startswith'),
                'value' => 'startswith',
            ],
        ];
    }
}
