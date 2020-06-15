<?php
namespace themes\clipone\views;

use packages\ticketing\{Department, Products, Department\Worktime};
use packages\userpanel\User;

trait DepartmentTrait {

	protected function getProductsForSelect() {
		$result = array();
		foreach (Products::get() as $product) {
			$result[] = array(
				"title" => $product->getTitle(),
				"value" => $product->getName(),
			);
		}
		return $result;
	}
	protected function getTranslatDays($day){
		switch($day) {
			case(Worktime::saturday):
				return t("ticketing.departments.worktime.saturday");
			case(Worktime::sunday):
				return t("ticketing.departments.worktime.sunday");
			case(Worktime::monday):
				return t("ticketing.departments.worktime.monday");
			case(Worktime::tuesday):
				return t("ticketing.departments.worktime.tuesday");
			case(Worktime::wednesday):
				return t("ticketing.departments.worktime.wednesday");
			case(Worktime::thursday):
				return t("ticketing.departments.worktime.thursday");
			case(Worktime::friday):
				return t("ticketing.departments.worktime.friday");
		}
	}

	protected function getDepartmentStatusForSelect(): array {
		return array(
			array(
				'title' => t('ticketing.departments.status.active'),
				'value' => Department::ACTIVE
			),
			array(
				'title' => t('ticketing.departments.status.deactive'),
				'value' => Department::DEACTIVE
			),
		);
	}

	protected function getDepartmentStatusLabel(Department $department): string {
		switch($department->status) {
			case (Department::ACTIVE):
				return '<span class="label label-success">' . t('ticketing.departments.status.active') . '</span>';
			case (Department::DEACTIVE):
					return '<span class="label label-warning">' . t('ticketing.departments.status.deactive') . '</span>';
		}
	}
}