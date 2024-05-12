<?php

namespace themes\clipone\Views\Ticketing\Settings\Department;

use packages\base\View\Error;
use packages\ticketing\Views\Settings\Department\ListView as DepartmentList;
use packages\userpanel;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\DepartmentTrait;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\ListTrait;
use themes\clipone\Views\Ticketing\HelperTrait;
use themes\clipone\ViewTrait;

class listview extends DepartmentList
{
    use DepartmentTrait;
    use FormTrait;
    use ListTrait;
    use ViewTrait;
    use HelperTrait;

    public function __beforeLoad()
    {
        $this->setTitle(t('departments'));
        $this->setButtons();
        $this->onSourceLoad();
        Navigation::active($this->getTicketingSettingsMenuItemName('departments'));
        if (empty($this->getDepartments())) {
            $this->addNotFoundError();
        }
    }

    private function addNotFoundError()
    {
        $error = new Error();
        $error->setType(Error::NOTICE);
        $error->setCode('ticketing.settings.department.notfound');
        $error->setMessage(t('ticketing.settings.department.notfound'));
        if ($this->canAdd) {
            $error->setData([
                [
                    'type' => 'btn-teal',
                    'txt' => t('add'),
                    'link' => userpanel\url('settings/departments/add'),
                ],
            ], 'btns');
        }
        $this->addError($error);
    }

    public function setButtons()
    {
        $this->setButton('edit', $this->canEdit, [
            'title' => t('department.edit'),
            'icon' => 'fa fa-edit',
            'classes' => ['btn', 'btn-xs', 'btn-teal'],
        ]);
        $this->setButton('delete', $this->canDel, [
            'title' => t('department.delete'),
            'icon' => 'fa fa-times',
            'classes' => ['btn', 'btn-xs', 'btn-bricky'],
        ]);
    }

    public function getComparisonsForSelect()
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

    public static function onSourceLoad()
    {
        parent::onSourceLoad();
        if (parent::$navigation) {
            $departments = new MenuItem('departments');
            $departments->setTitle(t('departments'));
            $departments->setURL(userpanel\url('settings/departments'));
            $departments->setIcon('fa fa-university');
            self::getTicketingSettingsMenu()->addItem($departments);
        }
    }
}
