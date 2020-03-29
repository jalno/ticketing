<?php
namespace packages\ticketing\listeners;

use packages\base\{Date, Options};
use packages\userpanel\{events\General\Settings as SettingsEvent, Usertype, User};
use packages\ticketing\controllers\Settings as Controller;

class Settings {
	
	public function init(SettingsEvent $settings){
		$setting = new SettingsEvent\Setting('ticketing');
		$setting->setController(Controller::class);
		$this->addRegisterItems($setting);
		$settings->addSetting($setting);
	}

	private function addRegisterItems(SettingsEvent\Setting $setting) {
		$setting->addInput(array(
			'name' => 'ticketing_autoclose_time',
			'type' => 'number',
			'min' => 1,
		));
		$setting->addField(array(
			'name' => 'ticketing_autoclose_time',
			'type' => 'number',
			'label' => t('settings.ticketing.autoclose_time'),
			'min' => 1,
			"ltr" => true,
			"input-group" => array(
				"right" => array(
					array(
						"type" => "addon",
						"text" => t("packaeges.ticketing.hour"),
					),
				),
			),
		));
		$options = Options::get("packages.ticketing.close.respitetime");
		$setting->setDataForm('ticketing_autoclose_time', $options ? $options / 3600 : "");
	}
}
