<?php

namespace packages\ticketing\Controllers;

use packages\base\Options;
use packages\userpanel\Events\General\Settings\Controller;
use packages\userpanel\Events\General\Settings\Log;

class Settings implements Controller
{
    public function store(array $inputs): array
    {
        $logs = [];
        if (isset($inputs['ticketing_autoclose_time'])) {
            $option = Options::get('packages.ticketing.close.respitetime');
            if (is_numeric($option)) {
                $option /= 3600;
            }
            if ($option != $inputs['ticketing_autoclose_time']) {
                $logs[] = new Log('ticketing_autoclose_time', $this->translateCloseTime($option), $this->translateCloseTime($inputs['ticketing_autoclose_time']), t('settings.ticketing.autoclose_time'));
                $option = $inputs['ticketing_autoclose_time'] * 3600;
                Options::save('packages.ticketing.close.respitetime', $option, true);
            }
        }

        if (isset($inputs['ticketing_send_notification_on_send_ticket'])) {
            $option = Options::get('packages.ticketing.send_notification_on_send_ticket');
            if ($option != $inputs['ticketing_send_notification_on_send_ticket']) {
                $logs[] = new Log(
                    'ticketing_send_notification_on_send_ticket',
                    $this->translateTriggerNotification($option),
                    $this->translateTriggerNotification($inputs['ticketing_send_notification_on_send_ticket']),
                    t('settings.ticketing.send_notification_on_send_ticket')
                );
                $option = $inputs['ticketing_send_notification_on_send_ticket'];
                Options::save('packages.ticketing.send_notification_on_send_ticket', $option, true);
            }
        }

        return $logs;
    }

    private function translateTriggerNotification(bool $value): string
    {
        return $value ? t('settings.ticketing.send_notification_on_send_ticket') : t('settings.ticketing.send.without_notification');
    }

    private function translateCloseTime(int $hours): string
    {
        if ($hours) {
            return t('packages.ticketing.autoclose_time.inHours', ['hour' => $hours]);
        }

        return t('settings.ticketing.autoclose_time.disable');
    }
}
