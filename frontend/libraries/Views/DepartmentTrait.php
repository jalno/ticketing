<?php

namespace themes\clipone\Views;

use packages\ticketing\Department;
use packages\ticketing\Department\WorkTime;
use packages\ticketing\Products;

trait DepartmentTrait
{
    protected function getProductsForSelect()
    {
        $result = [];
        foreach (Products::get() as $product) {
            $result[] = [
                'title' => $product->getTitle(),
                'value' => $product->getName(),
            ];
        }

        return $result;
    }

    protected function getTranslatDays($day)
    {
        switch ($day) {
            case WorkTime::saturday:
                return t('ticketing.departments.worktime.saturday');
            case WorkTime::sunday:
                return t('ticketing.departments.worktime.sunday');
            case WorkTime::monday:
                return t('ticketing.departments.worktime.monday');
            case WorkTime::tuesday:
                return t('ticketing.departments.worktime.tuesday');
            case WorkTime::wednesday:
                return t('ticketing.departments.worktime.wednesday');
            case WorkTime::thursday:
                return t('ticketing.departments.worktime.thursday');
            case WorkTime::friday:
                return t('ticketing.departments.worktime.friday');
        }
    }

    protected function getDepartmentStatusForSelect(): array
    {
        return [
            [
                'title' => t('ticketing.departments.status.active'),
                'value' => Department::ACTIVE,
            ],
            [
                'title' => t('ticketing.departments.status.deactive'),
                'value' => Department::DEACTIVE,
            ],
        ];
    }

    protected function getDepartmentStatusLabel(Department $department): string
    {
        switch ($department->status) {
            case Department::ACTIVE:
                return '<span class="label label-success">'.t('ticketing.departments.status.active').'</span>';
            case Department::DEACTIVE:
                return '<span class="label label-warning">'.t('ticketing.departments.status.deactive').'</span>';
            default:
                throw new \Exception('Department status is invalid');
        }
    }
}
