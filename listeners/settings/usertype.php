<?php
namespace packages\ticketing\listeners\settings;
use \packages\userpanel\usertype\permissions;
class usertype{
	public function permissions_list(){
		$permissions = array(
			'list',
			'add',
			'view',
			'reply',
			'edit',
			'lock',
			'unlock',
			'delete',
			'close',
			"unassigned",

			'message_delete',
			'message_edit',

			'files-download',

			'settings_departments_list',
			'settings_departments_add',
			'settings_departments_edit',
			'settings_departments_delete'

		);
		foreach($permissions as $permission){
			permissions::add('ticketing_'.$permission);
		}
	}
}
