<?php

namespace themes\clipone\Views\Ticketing\Settings\Department;

use packages\ticketing\Views\Settings\Department\Edit as DepartmentEdit;
use packages\userpanel\Date;
use themes\clipone\Navigation;
use themes\clipone\Views\DepartmentTrait;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\Ticketing\HelperTrait;
use themes\clipone\ViewTrait;

class Edit extends DepartmentEdit
{
    use DepartmentTrait;
    use FormTrait;
    use ViewTrait;
    use HelperTrait;

    protected $department;

    public function __beforeLoad()
    {
        $this->department = $this->getDepartment();
        $this->setTitle(t('department_edit'));
        Navigation::active($this->getTicketingSettingsMenuItemName('departments'));
        $this->addBodyClass('departments');
        $this->addBodyClass('departments-add');
    }

    protected function sortedDays()
    {
        $days = [];
        $firstDay = Date::getFirstDayOfWeek();
        for ($i = $firstDay; $i < $firstDay + 7; ++$i) {
            $days[] = [
                'day' => ($i % 7),
            ];
        }

        return $days;
    }
}
