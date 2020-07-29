<?php
namespace packages\ticketing\controllers;

use packages\userpanel;
use packages\base\{Date, Options, View};
use packages\userpanel\{events\General\Settings as Event, events\General\Settings\Controller, events\General\Settings\Log, Authorization, Authentication, UserType, User, Logs};

class Settings implements Controller {
	public function store(array $inputs): array {
		$logs = array();
		function translateCloseTime($hours) {
			if ($hours) {
				return t("packages.ticketing.autoclose_time.inHours", array("hour" => $hours));
			}
			return t("settings.ticketing.autoclose_time.disable");
		}
		if (isset($inputs["ticketing_autoclose_time"])) {
			$option = Options::get("packages.ticketing.close.respitetime");
			if (is_numeric($option)) {
				$option /= 3600;
			}
			if ($option != $inputs["ticketing_autoclose_time"]) {
				$logs[] = new Log('ticketing_autoclose_time', translateCloseTime($option), translateCloseTime($inputs["ticketing_autoclose_time"]), t('settings.ticketing.autoclose_time'));
				$option = $inputs["ticketing_autoclose_time"] * 3600;
				Options::save("packages.ticketing.close.respitetime", $option, true);
			}
		}

		function translateTriggerNotification($value) {
			return ($value ? t('settings.ticketing.send.with_notification') : t('settings.ticketing.send.without_notification'));
		}
		if (isset($inputs['ticketing_send_notification_default_behaviour'])) {
			$option = Options::get('packages.ticketing.send.notification_default_behaviour');
			if ($option != $inputs['ticketing_send_notification_default_behaviour']) {
				$logs[] = new Log(
					'ticketing_send_notification_default_behaviour',
					translateTriggerNotification($option),
					translateTriggerNotification($inputs['ticketing_send_notification_default_behaviour']),
					t('settings.ticketing.send.notification_default_behaviour')
				);
				$option = $inputs['ticketing_send_notification_default_behaviour'];
				Options::save('packages.ticketing.send.notification_default_behaviour', $option, true);
			}
		}
		return $logs;
	}
}
