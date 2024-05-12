<?php

namespace themes\clipone\Views\Ticketing\Settings\Labels;

use packages\base\Views\Traits\Form;
use packages\ticketing\Authorization;
use packages\ticketing\Department;
use function packages\userpanel\Url;
use packages\userpanel\Views\ListView;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\ListTrait;
use themes\clipone\Views\Ticketing\HelperTrait;
use themes\clipone\Views\Ticketing\LabelTrait;
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
    use LabelTrait;
    use HelperTrait;

    public static function onSourceLoad(): void
    {
        if (Authorization::is_accessed('settings_labels_search')) {
            $labels = new MenuItem('ticketing_settings_labels');
            $labels->setTitle(t('titles.ticketing.labels'));
            $labels->setURL(url('settings/ticketing/labels'));
            $labels->setIcon('fa fa-tag');

            self::getTicketingSettingsMenu()->addItem($labels);
        }
    }

    public bool $canAdd = false;
    public bool $canEdit = false;
    public bool $canDelete = false;

    public function __construct(...$args)
    {
        parent::__construct(...$args);

        $this->canAdd = Authorization::is_accessed('settings_labels_add');
        $this->canEdit = Authorization::is_accessed('settings_labels_edit');
        $this->canDelete = Authorization::is_accessed('settings_labels_delete');
    }

    public function __beforeLoad(): void
    {
        $this->setTitle(t('titles.ticketing.labels'));
        $this->addBodyClass('ticketing-labels');
        $this->setButtons();

        Navigation::active($this->getTicketingSettingsMenuItemName('ticketing_settings_labels'));
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
