<?php
namespace packages\ticketing;
use \packages\userpanel\authorization as UserPanelAuthorization;
use \packages\ticketing\authentication;
class authorization extends UserPanelAuthorization{
	static function is_accessed($permission, $prefix = 'ticketing'){
		return parent::is_accessed($permission, $prefix);
	}
	static function haveOrFail($permission, $prefix = 'ticketing'){
		parent::haveOrFail($permission, $prefix);
	}
}
