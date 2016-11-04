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

			'message_delete',
			'message_edit',

			'files_download'

		);
		foreach($permissions as $permission){
			permissions::add('ticketing_'.$permission);
		}
	}
}
