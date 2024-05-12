<?php

namespace themes\clipone\Views\Ticketing\Settings\Department;

use packages\ticketing\Views\Settings\Department\Add as DepartmentAdd;
use packages\userpanel;
use packages\userpanel\Date;
use themes\clipone\Navigation;
use themes\clipone\Views\DepartmentTrait;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\Ticketing\HelperTrait;
use themes\clipone\ViewTrait;

class Add extends DepartmentAdd
{
    use DepartmentTrait;
    use FormTrait;
    use ViewTrait;
    use HelperTrait;

    protected $days = [];

    public function __beforeLoad()
    {
        $this->setTitle([
            t('settings'),
            t('departments'),
            t('department_add'),
        ]);
        $this->setNavigation();
        $this->setDaysValue();
        Navigation::active($this->getTicketingSettingsMenuItemName('departments'));
        $this->addBodyClass('departments');
        $this->addBodyClass('departments-add');
    }

    private function setNavigation()
    {
        $item = Navigation::getByName('settings');
        $departments = new Navigation\MenuItem('departments');
        $departments->setTitle(t('departments'));
        $departments->setURL(userpanel\url('settings/departments'));
        $departments->setIcon('fa fa-university');
        $item->addItem($departments);
    }

    private function setDaysValue()
    {
        $firstDay = Date::getFirstDayOfWeek();
        for ($i = $firstDay; $i < $firstDay + 7; ++$i) {
            $this->days[] = [
                'day' => ($i % 7),
            ];
        }
    }

    protected function sortedDays()
    {
        return $this->days;
    }
}
