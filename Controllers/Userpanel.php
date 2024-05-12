<?php

namespace packages\ticketing\Controllers;

use packages\base\DB;
use packages\base\DB\Parenthesis;
use packages\base\NotFound;
use packages\base\Response;
use packages\ticketing\Authorization;
use packages\ticketing\Controller;
use packages\ticketing\Department;
use packages\userpanel\User;

class UserPanel extends Controller
{
    protected $authentication = true;

    public function operators($data): Response
    {
        Authorization::haveOrFail('edit');
        $department = Department::byId($data['department']);
        if (!$department) {
            throw new NotFound();
        }
        $inputs = $this->checkinputs([
            'word' => [],
        ]);
        $this->response->setStatus(true);
        $users = $department->users;
        $priority = DB::subQuery();
        $priority->setQueryOption('DISTINCT');
        $priority->get('userpanel_usertypes_priorities', null, 'parent');
        $permission = DB::subQuery();
        $permission->where('name', 'ticketing_view');
        $permission->get('userpanel_usertypes_permissions', null, 'type');
        $model = new User();
        $model->where('type', $priority, 'IN');
        $model->where('type', $permission, 'IN');
        if ($users) {
            $model->where('id', $users, 'IN');
        }
        $parenthesis = new Parenthesis();
        foreach (['name', 'lastname', 'email', 'cellphone'] as $item) {
            $parenthesis->orWhere($item, $inputs['word'], 'contains');
        }
        $parenthesis->orWhere("CONCAT(`name`, ' ', `lastname`)", $inputs['word'], 'contains');
        $model->where($parenthesis);
        $this->response->setData($model->arrayBuilder()->get(), 'items');

        return $this->response;
    }
}
