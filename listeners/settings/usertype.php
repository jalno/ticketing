<?php
namespace packages\ticketing\listeners\settings;
use \packages\userpanel\usertype\permissions;
class usertype{
	public function permissions_list(){
		$permissions = array(
			'list',
			'add',
			'add_multiuser',
			'add_override-force-product-choose',
			'view',
			'reply',
			'edit',
			'lock',
			'unlock',
			'delete',
			'close',
			'unassigned',
			'enable_disabled_notification',

			'message_delete',
			'message_edit',

			'files-download',

			'settings_departments_list',
			'settings_departments_add',
			'settings_departments_edit',
			'settings_departments_delete',

			'settings_templates_search',
			'settings_templates_add',
			'settings_templates_edit',
			'settings_templates_delete',

			'use_templates',
		);
		foreach($permissions as $permission){
			permissions::add('ticketing_'.$permission);
		}
	}
}
