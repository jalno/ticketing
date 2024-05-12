<?php

namespace packages\ticketing\Views\Settings\Department;

class Delete extends \packages\ticketing\View
{
    protected $department;

    public function setDepartmentData($department)
    {
        $this->department = $department;
    }

    public function getDepartmentData()
    {
        return $this->department;
    }
}
