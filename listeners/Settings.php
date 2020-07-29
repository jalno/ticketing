<?php
namespace packages\ticketing\listeners;

use packages\base\{Date, Options};
use packages\userpanel\{events\General\Settings as SettingsEvent, Usertype, User};
use packages\ticketing\{controllers\Settings as Controller, Ticket};

class Settings {
	
	public function init(SettingsEvent $settings): void {
		$setting = new SettingsEvent\Setting("ticketing");
		$setting->setController(Controller::class);
		$this->addRegisterItems($setting);
		$settings->addSetting($setting);
	}

	private function addRegisterItems(SettingsEvent\Setting $setting): void {
		$setting->addInput(array(
			"name" => "ticketing_autoclose_time",
			"type" => "number",
			"min" => 0,
			"empty" => true,
			"zero" => true,
			"default" => 0
		));
		$setting->addField(array(
			"name" => "ticketing_autoclose_time",
			"type" => "number",
			"label" => t("settings.ticketing.autoclose_time"),
			"min" => 1,
			"ltr" => true,
			"placeholder" => t("settings.ticketing.autoclose_time.disable"),
			"input-group" => array(
				"right" => array(
					array(
						"type" => "addon",
						"text" => t("packages.ticketing.hour"),
					),
				),
			),
		));
		$options = Options::get("packages.ticketing.close.respitetime");
		$setting->setDataForm("ticketing_autoclose_time", $options ? intval($options / 3600) : "");

		$setting->addInput(array(
			'name' => 'ticketing_send_notification_default_behaviour',
			'type' => 'number',
			'values' => [Ticket::SEND_WITH_NOTIFICATION, Ticket::SEND_WITHOUT_NOTIFICATION],
		));
		$setting->addField(array(
			'name' => 'ticketing_send_notification_default_behaviour',
			'type' => 'radio',
			'label' => t('settings.ticketing.send.notification_default_behaviour'),
			'inline' => true,
			'options' => array(
				array(
					'label' => t('settings.ticketing.send.with_notification'),
					'value' => Ticket::SEND_WITH_NOTIFICATION,
				),
				array(
					'label' => t('settings.ticketing.send.without_notification'),
					'value' => Ticket::SEND_WITHOUT_NOTIFICATION,
				),
			),
		));
		$options = Options::get('packages.ticketing.send.notification_default_behaviour');
		$setting->setDataForm("ticketing_send_notification_default_behaviour", $options);
	}
}
