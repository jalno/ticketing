<?php

namespace packages\ticketing\Controllers\Settings;

use packages\base\DB;
use packages\base\DB\Parenthesis;
use packages\base\Http;
use packages\base\InputValidation;
use packages\base\InputValidationException;
use packages\base\NotFound;
use packages\base\Response;
use packages\base\Translator;
use packages\base\Utility\Safe;
use packages\base\View\Error;
use packages\base\Views\FormError;
use packages\ticketing\Authentication;
use packages\ticketing\Authorization;
use packages\ticketing\Controller;
use packages\ticketing\Department;
use packages\ticketing\Logs;
use packages\ticketing\Products;
use packages\ticketing\Ticket;
use packages\ticketing\View;
use themes\clipone\Views\Ticketing as Views;
use packages\userpanel;
use packages\userpanel\Log;
use packages\userpanel\User;

class Departments extends Controller
{
    protected $authentication = true;

    public function listview()
    {
        Authorization::haveOrFail('settings_departments_list');
        $view = View::byName(Views\Settings\Department\ListView::class);
        $this->response->setView($view);
        $department = new Department();
        $inputRules = [
            'id' => [
                'type' => 'number',
                'optional' => true,
                'empty' => true,
            ],
            'title' => [
                'type' => 'string',
                'optional' => true,
                'empty' => true,
            ],
            'status' => [
                'type' => 'number',
                'optional' => true,
                'values' => Department::STATUSES,
            ],
            'word' => [
                'type' => 'string',
                'optional' => true,
                'empty' => true,
            ],
            'comparison' => [
                'values' => ['equals', 'startswith', 'contains'],
                'default' => 'contains',
                'optional' => true,
            ],
        ];
        $inputs = $this->checkinputs($inputRules);
        $department = new Department();
        foreach (['id', 'title', 'status'] as $item) {
            if (isset($inputs[$item]) and $inputs[$item]) {
                $comparison = $inputs['comparison'];
                if (in_array($item, ['id', 'status'])) {
                    $comparison = 'equals';
                }
                $department->where($item, $inputs[$item], $comparison);
            }
        }
        if (isset($inputs['word']) and $inputs['word']) {
            $parenthesis = new Parenthesis();
            foreach (['title'] as $item) {
                if (isset($inputs[$item]) or $inputs[$item]) {
                    $parenthesis->where($item, $inputs['word'], $inputs['comparison'], 'OR');
                }
            }
            $department->where($parenthesis);
        }
        $view->setDataForm($this->inputsvalue($inputRules));
        $department->pageLimit = $this->items_per_page;
        $departments = $department->paginate($this->page);
        $view->setDataList($departments);
        $view->setPaginate($this->page, $department->totalCount, $this->items_per_page);
        $this->response->setStatus(true);

        return $this->response;
    }

    public function delete($data)
    {
        Authorization::haveOrFail('settings_departments_delete');
        $department = Department::byId($data['id']);
        if (!$department) {
            throw new NotFound();
        }
        $view = View::byName(Views\Settings\Department\Delete::class);
        $view->setDepartmentData($department);
        $this->response->setStatus(false);
        if (HTTP::is_post()) {
            try {
                $ticket = new Ticket();
                $ticket->where('department', $department->id);
                if ($ticket->has()) {
                    throw new TicketDependencies();
                }

                $log = new Log();
                $log->user = Authentication::getID();
                $log->type = Logs\Settings\Departments\Delete::class;
                $log->title = Translator::trans('ticketing.logs.settings.departments.delete', ['department_id' => $department->id, 'department_title' => $department->title]);
                $log->parameters = ['department' => $department];
                $log->save();

                $department->delete();
                $this->response->setStatus(true);
                $this->response->Go(userpanel\url('settings/departments'));
            } catch (InputValidation $error) {
                $view->setFormError(FormError::fromException($error));
            } catch (TicketDependencies $e) {
                $error = new Error();
                $error->setCode('ticketDependencies');
                $error->setMessage(Translator::trans('error.ticketDependencies', ['ticket_search_link' => userpanel\url('ticketing', ['department' => $department->id])]));
                $view->addError($error);
            }
        } else {
            $this->response->setStatus(true);
        }
        $this->response->setView($view);

        return $this->response;
    }

    public function add()
    {
        Authorization::haveOrFail('settings_departments_add');
        $this->response->setView($view = View::byName(Views\Settings\Department\Add::class));
        $view->setUsers($this->getUsersForSelect());
        $this->response->setStatus(true);

        return $this->response;
    }

    public function store(): Response
    {
        Authorization::haveOrFail('settings_departments_add');
        $view = View::byName(Views\Settings\Department\Add::class);
        $this->response->setView($view);
        $usersForSelect = $this->getUsersForSelect();
        $view->setUsers($usersForSelect);
        $products = Products::get();
        $inputs = $this->checkinputs([
            'title' => [
                'type' => 'string',
            ],
            'status' => [
                'type' => 'number',
                'values' => Department::STATUSES,
            ],
            'products' => [
                'type' => function ($data, $rule, $input) use (&$products) {
                    if (!is_string($data)) {
                        throw new InputValidationException($input);
                    }
                    $selectedProducts = ($data ? explode(',', $data) : []);
                    $existProducts = array_map(function ($item) {
                        return $item->getName();
                    }, $products);
                    if (array_diff($selectedProducts, $existProducts)) {
                        throw new InputValidationException($input);
                    }

                    return $selectedProducts;
                },
            ],
            'mandatory_choose_product' => [
                'type' => 'bool',
                'default' => false,
                'optional' => true,
            ],
            'day' => [
                'type' => function ($data, $rule, $input) {
                    if (!is_array($data)) {
                        throw new InputValidationException($input);
                    }
                    foreach ($data as $day => $val) {
                        if (!in_array($day, range(0, 6))) {
                            throw new InputValidationException("day[{$day}][enable]");
                        }
                        if (isset($val['enable']) and $val['enable']) {
                            if (!isset($val['worktime']['start']) or !in_array($val['worktime']['start'], range(0, 23))) {
                                throw new InputValidationException("day[{$day}][worktime][start]");
                            }
                            if (!isset($val['worktime']['end']) or !in_array($val['worktime']['end'], range(0, 23))) {
                                throw new InputValidationException("day[{$day}][worktime][end]");
                            }
                            if ($val['worktime']['end'] < $val['worktime']['start']) {
                                throw new InputValidationException("day[{$day}][worktime][end]");
                            }
                        } else {
                            $data[$day]['worktime']['start'] = $data[$day]['worktime']['end'] = 0;
                        }
                        if (isset($val['message']) and $val['message']) {
                            $val['message'] = Safe::string($val['message']);
                        }
                    }

                    return $data;
                },
            ],
            'users' => [
                'type' => function ($data, $rule, $input) use ($usersForSelect) {
                    if (!is_array($data)) {
                        throw new InputValidationException($input);
                    }
                    $users = [];
                    foreach ($usersForSelect as $user) {
                        $users[] = $user->id;
                    }
                    foreach ($data as $key => $id) {
                        if (!in_array($id, $users)) {
                            throw new InputValidationException("users[{$key}]");
                        }
                    }

                    return $data;
                },
                'optional' => true,
            ],
        ]);
        $department = new Department();
        $department->title = $inputs['title'];
        $department->status = $inputs['status'];
        if (isset($inputs['users'])) {
            $department->users = array_values($inputs['users']);
        }
        $department->save();
        $department->setMandatoryChooseProduct($inputs['mandatory_choose_product']);
        if (isset($inputs['products']) and $inputs['products']) {
            $department->setProducts($inputs['products']);
        }
        foreach (Department\WorkTime::getDays() as $day) {
            if (!isset($inputs['day'][$day])) {
                continue;
            }
            $input = $inputs['day'][$day];
            $work = new Department\WorkTime();
            $work->day = $day;
            $work->department = $department->id;
            $work->time_start = $input['worktime']['start'];
            $work->time_end = $input['worktime']['end'];
            $work->message = isset($input['message']) ? $input['message'] : null;
            $work->save();
        }
        $log = new Log();
        $log->user = Authentication::getID();
        $log->type = Logs\Settings\Departments\Add::class;
        $log->title = t('ticketing.logs.settings.departments.add', ['department_id' => $department->id, 'department_title' => $department->title]);
        $log->save();
        $this->response->setStatus(true);
        $this->response->Go(userpanel\url('settings/departments/edit/'.$department->id));

        return $this->response;
    }

    public function edit($data)
    {
        Authorization::haveOrFail('settings_departments_edit');
        $department = Department::byId($data['id']);
        if (!$department) {
            throw new NotFound();
        }
        $view = View::byName(Views\Settings\Department\Edit::class);
        $this->response->setView($view);
        $view->setDepartment($department);
        $view->setUsers($this->getUsersForSelect());
        $this->response->setStatus(true);

        return $this->response;
    }

    public function update($data): Response
    {
        Authorization::haveOrFail('settings_departments_edit');
        $view = View::byName(Views\Settings\Department\Edit::class);
        $this->response->setView($view);
        $department = Department::byId($data['id']);
        if (!$department) {
            throw new NotFound();
        }
        $view->setDepartment($department);
        $usersForSelect = $this->getUsersForSelect();
        $view->setUsers($usersForSelect);
        $products = Products::get();
        $inputs = $this->checkinputs([
            'title' => [
                'type' => 'string',
                'optional' => true,
            ],
            'status' => [
                'type' => 'number',
                'optional' => true,
                'values' => Department::STATUSES,
            ],
            'products' => [
                'type' => function ($data, $rule, $input) use (&$products) {
                    if (!is_string($data)) {
                        throw new InputValidationException($input);
                    }
                    $selectedProducts = ($data ? explode(',', $data) : []);
                    $existProducts = array_map(function ($item) {
                        return $item->getName();
                    }, $products);
                    if (array_diff($selectedProducts, $existProducts)) {
                        throw new InputValidationException($input);
                    }

                    return $selectedProducts;
                },
            ],
            'mandatory_choose_product' => [
                'type' => 'bool',
                'default' => false,
                'optional' => true,
            ],
            'day' => [
                'type' => function ($data, $rule, $input) {
                    if (!is_array($data)) {
                        throw new InputValidationException($input);
                    }
                    foreach ($data as $day => $val) {
                        if (!in_array($day, range(0, 6))) {
                            throw new InputValidationException("day[{$day}][enable]");
                        }
                        if (isset($val['enable']) and $val['enable']) {
                            if (!isset($val['worktime']['start']) or !in_array($val['worktime']['start'], range(0, 23))) {
                                throw new InputValidationException("day[{$day}][worktime][start]");
                            }
                            if (!isset($val['worktime']['end']) or !in_array($val['worktime']['end'], range(0, 23))) {
                                throw new InputValidationException("day[{$day}][worktime][end]");
                            }
                            if ($val['worktime']['end'] < $val['worktime']['start']) {
                                throw new InputValidationException("day[{$day}][worktime][end]");
                            }
                        } else {
                            $data[$day]['worktime']['start'] = $data[$day]['worktime']['end'] = 0;
                        }
                        if (isset($val['message']) and $val['message']) {
                            $val['message'] = Safe::string($val['message']);
                        }
                    }

                    return $data;
                },
            ],
            'users' => [
                'type' => function ($data, $rule, $input) use (&$usersForSelect) {
                    if (!is_array($data)) {
                        throw new InputValidationException($input);
                    }
                    $users = [];
                    foreach ($usersForSelect as $user) {
                        $users[] = $user->id;
                    }
                    foreach ($data as $key => $id) {
                        if (!in_array($id, $users)) {
                            throw new InputValidationException("users[{$key}]");
                        }
                    }

                    return $data;
                },
                'optional' => true,
            ],
        ]);
        $parameters = [
            'oldData' => [],
            'newData' => [],
        ];
        foreach (['title', 'status'] as $item) {
            if (isset($inputs[$item]) and $department->$item != $inputs[$item]) {
                $parameters['oldData'][$item] = $department->$item;
                $parameters['newData'][$item] = $inputs[$item];
                $department->$item = $inputs[$item];
            }
        }
        if (isset($inputs['users'])) {
            if ($department->users) {
                $users = array_values($inputs['users']);
                foreach ($department->users as $user) {
                    if ($key = false !== array_search($user, $users)) {
                        unset($users[$key]);
                    } else {
                        $parameters['oldData']['users'][] = $user;
                    }
                }
                if ($users) {
                    $parameters['newData']['users'][] = $users;
                }
            }
            $department->users = array_values($inputs['users']);
        } elseif ($department->users) {
            $parameters['oldData']['users'] = $department->users;
            $department->users = null;
        }
        $department->save();
        if (isset($inputs['products'])) {
            $products = $department->getProducts();
            if (array_diff($products, $inputs['products']) or array_diff($inputs['products'], $products)) {
                $parameters['oldData']['products'] = $products;
                $parameters['newData']['products'] = $inputs['products'];
                $department->setProducts($inputs['products']);
            }
        }
        if (isset($inputs['mandatory_choose_product'])) {
            $isMandatory = $department->isMandatoryChooseProduct();
            if ($isMandatory != $inputs['mandatory_choose_product']) {
                $department->setMandatoryChooseProduct($inputs['mandatory_choose_product']);
                $parameters['oldData']['mandatory_choose_product'] = $isMandatory;
                $parameters['newData']['mandatory_choose_product'] = $inputs['mandatory_choose_product'];
            }
        }
        $days = Department\WorkTime::getDays();
        foreach ($days as $key => $day) {
            $input = null;
            $work = new Department\WorkTime();
            $work->where('day', $day);
            $work->where('department', $department->id);
            if (!$work = $work->getOne()) {
                continue;
            }
            if (isset($inputs['day'][$day])) {
                $input = $inputs['day'][$day];
                if (
                    $work->time_start != $input['worktime']['start']
                    or $work->time_end != $input['worktime']['end']
                    or $work->message != $input['message']
                ) {
                    $parameters['oldData']['worktimes'][] = $work;
                }

                $work->time_start = $input['worktime']['start'];
                $work->time_end = $input['worktime']['end'];
                $work->message = $input['message'];
                $work->save();
                unset($inputs['day'][$day]);
            } else {
                $parameters['oldData']['worktimes'][] = $work;
                $work->delete();
            }
        }
        foreach ($inputs['day'] as $day => $item) {
            $work = new Department\WorkTime();
            $work->day = $day;
            $work->department = $department->id;
            $work->time_start = $item['worktime']['start'];
            $work->time_end = $item['worktime']['end'];
            $work->message = $item['message'];
            $work->save();
            $parameters['newData']['worktimes'][] = $work;
        }
        $log = new Log();
        $log->user = Authentication::getID();
        $log->type = Logs\Settings\Departments\Edit::class;
        $log->title = t('ticketing.logs.settings.departments.edit', ['department_id' => $department->id, 'department_title' => $department->title]);
        $log->parameters = $parameters;
        $log->save();
        $this->response->setStatus(true);

        return $this->response;
    }

    protected function getUsersForSelect(): array
    {
        $priority = DB::subQuery();
        $priority->setQueryOption('DISTINCT');
        $priority->get('userpanel_usertypes_priorities', null, 'parent');
        $permission = DB::subQuery();
        $permission->where('name', 'ticketing_view');
        $permission->get('userpanel_usertypes_permissions', null, 'type');
        $user = new User();
        $user->where('type', $priority, 'IN');
        $user->where('type', $permission, 'IN');

        return $user->get();
    }
}
