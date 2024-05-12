<?php

namespace themes\clipone\Views\Ticketing\Settings\Department;

use packages\base\Translator;
use packages\ticketing\Views\Settings\Department\Delete as DepartmentDelete;
use packages\userpanel;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\ListTrait;
use themes\clipone\Views\Ticketing\HelperTrait;
use themes\clipone\ViewTrait;

class Delete extends DepartmentDelete
{
    use ViewTrait;
    use ListTrait;
    use HelperTrait;

    protected $messages;

    public function __beforeLoad()
    {
        $this->setTitle(t('department.delete.warning.title'));
        $this->setNavigation();
        Navigation::active($this->getTicketingSettingsMenuItemName('departments'));
    }

    private function setNavigation()
    {
        $item = Navigation::getByName('settings');
        $departments = new MenuItem('departments');
        $departments->setTitle(Translator::trans('departments'));
        $departments->setURL(userpanel\url('settings/departments'));
        $departments->setIcon('fa fa-university');
        $item->addItem($departments);
    }
}
