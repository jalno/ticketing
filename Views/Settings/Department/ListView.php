<?php

namespace packages\ticketing\Views\Settings\Department;

use packages\base\Views\Traits\Form as FormTrait;
use packages\ticketing\Authorization;

class ListView extends \packages\ticketing\Views\ListView
{
    use FormTrait;
    protected $canAdd;
    protected $canEdit;
    protected $canDel;
    protected static $navigation;

    public function __construct()
    {
        $this->canAdd = Authorization::is_accessed('settings_departments_add');
        $this->canEdit = Authorization::is_accessed('settings_departments_edit');
        $this->canDel = Authorization::is_accessed('settings_departments_delete');
    }

    public function getDepartments()
    {
        return $this->dataList;
    }

    public static function onSourceLoad()
    {
        self::$navigation = Authorization::is_accessed('settings_departments_list');
    }
}