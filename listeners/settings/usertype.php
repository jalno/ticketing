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

			'message_delete',
			'message_edit',

			'files_download',

			'department_list',
			'department_add',
			'department_edit',
			'department_delete'

		);
		foreach($permissions as $permission){
			permissions::add('ticketing_'.$permission);
		}
	}
}
