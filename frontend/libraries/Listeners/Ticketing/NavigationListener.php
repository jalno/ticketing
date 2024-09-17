<?php
namespace themes\clipone\Listeners\Ticketing;

use packages\Ticketing\Authorization;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\Dashboard;

use function packages\userpanel\url;

class NavigationListener
{
	public function initial(): void
	{
		if (Authorization::is_accessed('list')) {
			$item = new MenuItem('ticketing');
			$item->setTitle(t('ticketing'));
			$item->setURL(url('ticketing'));
			$item->setIcon('clip-user-6');
			$item->setPriority(280);
			Navigation::addItem($item);
		}

		if (Authorization::is_accessed('settings_departments_list')) {
			$departments = new MenuItem('departments');
			$departments->setTitle(t('departments'));
			$departments->setURL(url('settings/departments'));
			$departments->setIcon('fa fa-university');
			$this->getSettings()->addItem($departments);
		}

		if (Authorization::is_accessed('settings_labels_search')) {
			$labels = new MenuItem('ticketing_settings_labels');
			$labels->setTitle(t('titles.ticketing.labels'));
			$labels->setURL(url('settings/ticketing/labels'));
			$labels->setIcon('fa fa-tag');

			$this->getSettings()->addItem($labels);
		}

		if (Authorization::is_accessed('settings_templates_search')) {
			$templates = new MenuItem('ticketing_settings_templates');
			$templates->setTitle(t('titles.ticketing.templates'));
			$templates->setURL(url('settings/ticketing/templates'));
			$templates->setIcon('fa fa-file-text-o');

			$this->getSettings()->addItem($templates);
		}
	}

	public function getSettings(): MenuItem
	{
		$settings = Navigation::getByName('settings/ticketing');
		if (!$settings) {
			$settings = new MenuItem('ticketing');
			$settings->setTitle(t('titles.settings_ticketing'));
			$settings->setIcon('clip-user-6');

			Dashboard::getSettingsMenu()->addItem($settings);
		}

		return $settings;
	}
}