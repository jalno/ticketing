<?php
namespace packages\ticketing\Listeners\Settings;
use \packages\userpanel\UserType\Permissions;
class UserType{
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

			'settings_labels_search',
			'settings_labels_add',
			'settings_labels_edit',
			'settings_labels_delete',

			'view_labels',
		);
		foreach($permissions as $permission){
			Permissions::add('ticketing_'.$permission);
		}
	}
}
