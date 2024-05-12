<?php

namespace packages\ticketing;

use packages\base\DB\DBObject;
use packages\ticketing\Department\WorkTime;
use packages\userpanel\Date;

class Department extends DBObject
{
    use Paramable;

    public const ACTIVE = 1;
    public const DEACTIVE = 2;

    public const STATUSES = [
        self::ACTIVE,
        self::DEACTIVE,
    ];

    protected $dbTable = 'ticketing_departments';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'title' => ['type' => 'text', 'required' => true],
        'users' => ['type' => 'text'],
        'status' => ['type' => 'int', 'required' => true],
    ];
    protected $jsonFields = ['users'];
    protected $relations = [
        'worktimes' => ['hasMany', WorkTime::class, 'department'],
    ];

    public function setProducts(array $products): void
    {
        $this->setParam('products', $products);
    }

    public function getProducts(): array // if products is an empty array, all products are accpetable for this department
    {return $this->param('products') ?? [];
    }

    public function setMandatoryChooseProduct(bool $isMandatory): void
    {
        $this->setParam('mandatory_choose_product', $isMandatory);
    }

    public function isMandatoryChooseProduct(): bool
    {
        return (bool) $this->param('mandatory_choose_product');
    }

    protected function isWorking()
    {
        $worktime = $this->currentWork();
        if (!$worktime) {
            return false;
        }

        return $worktime->time_start <= Date::format('H') and $worktime->time_end >= Date::format('H');
    }

    protected function currentWork()
    {
        foreach ($this->worktimes as $worktime) {
            if ($worktime->day == Date::format('N')) {
                return $worktime;
            }
        }
    }
    protected $addworktimes = [];

    public function addWorkTimes()
    {
        $this->data['worktimes'] = [];
        for ($x = 1; 8 != $x; ++$x) {
            $work = new WorkTime([
                'department' => $this->id,
                'day' => $x,
            ]);
            $work->save();
            $this->data['worktimes'][] = $work;
        }
    }

    public function save($data = null)
    {
        $isNew = $this->isNew;
        if ($return = parent::save($data)) {
            if ($isNew) {
                $this->addWorkTimes();
            }
        }

        return $return;
    }
}
