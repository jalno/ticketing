<?php

namespace themes\clipone\Views\Ticketing\Settings\Department;

use packages\base\View\Error;
use packages\ticketing\Views\Settings\Department\ListView as DepartmentList;
use packages\userpanel;
use themes\clipone\Navigation;
use themes\clipone\Views\DepartmentTrait;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\ListTrait;
use themes\clipone\Views\Ticketing\HelperTrait;
use themes\clipone\ViewTrait;

class ListView extends DepartmentList
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
}
