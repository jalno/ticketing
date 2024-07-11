<?php

namespace packages\ticketing\Views\Settings\Department;

use packages\base\DB\DBObject;
use packages\ticketing\Department;
use packages\ticketing\Views\Form;

class Edit extends Form
{
    public function setDepartment(Department $department)
    {
        $this->setData($department, 'department');
        $this->setDataForm($department->title, 'title');
        $this->setDataForm($department->status, 'status');
        $this->setDataForm($department->getProducts(), 'products-select');
        $this->setDataForm($department->isMandatoryChooseProduct(), 'mandatory_choose_product');
        foreach ($department->worktimes as $work) {
            $this->setDataForm($work->time_start or $work->time_end, "day[{$work->day}][enable]");
            $this->setDataForm($work->time_start, "day[{$work->day}][worktime][start]");
            $this->setDataForm($work->time_end, "day[{$work->day}][worktime][end]");
            $this->setDataForm($work->message, "day[{$work->day}][message]");
        }
        if ($department->users) {
            foreach ($department->users as $user) {
                $this->setDataForm($user, "users[{$user}]");
            }
        } else {
            $this->setDataForm('all', 'allUsers');
        }
    }

    public function getDepartment()
    {
        return $this->getData('department');
    }

    public function export(): array
    {
        $department = $this->getDepartment();
        $data = [
            'department' => $department->toArray(),
        ];
        if ($currentWork = $department->currentWork()) {
            $data['department']['currentWork'] = $currentWork->toArray();
        }

        return [
            'data' => $data,
            'users' => DBObject::objectToArray($this->getUsers()),
        ];
    }

    public function setUsers(array $users)
    {
        $this->setData($users, 'users');
    }

    protected function getUsers(): array
    {
        $users = $this->getData('users');
        if (!$users) {
            $users = [];
        }

        return $users;
    }
}
