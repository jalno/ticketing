<?php
namespace packages\ticketing;
use \packages\userpanel\Authorization as UserPanelAuthorization;
use \packages\ticketing\Authentication;
class Authorization extends UserPanelAuthorization{
	static function is_accessed($permission, $prefix = 'ticketing'){
		return parent::is_accessed($permission, $prefix);
	}
	static function haveOrFail($permission, $prefix = 'ticketing'){
		parent::haveOrFail($permission, $prefix);
	}
}
