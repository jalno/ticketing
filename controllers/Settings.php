<?php
namespace packages\ticketing\controllers;

use packages\userpanel;
use packages\base\{Date, Options, View};
use packages\userpanel\{events\General\Settings as Event, events\General\Settings\Controller, events\General\Settings\Log, Authorization, Authentication, UserType, User, Logs};

class Settings implements Controller {
	public function store(array $inputs): array {
		$logs = array();
		$option = Options::get("packages.ticketing.close.respitetime");
		if (isset($inputs["ticketing_autoclose_time"])) {
			if (!$option or $option != $inputs["ticketing_autoclose_time"]) {
				$logs[] = new Log('ticketing_autoclose_time', t("packaeges.ticketing.hour", array("hour" => $option / 3600)), t("packaeges.ticketing.hour", array("hour" => $inputs["ticketing_autoclose_time"])), t('settings.ticketing.autoclose_time'));
				$option = $inputs["ticketing_autoclose_time"] * 3600;
			}
		}
		Options::save("packages.ticketing.close.respitetime", $option, true);
		return $logs;
	}

}
