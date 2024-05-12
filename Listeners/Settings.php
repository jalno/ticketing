<?php

namespace packages\ticketing\Listeners;

use packages\base\Options;
use packages\ticketing\Controllers\Settings as Controller;
use packages\userpanel\Events\General\Settings as SettingsEvent;

class Settings
{
    public function init(SettingsEvent $settings): void
    {
        $setting = new SettingsEvent\Setting('ticketing');
        $setting->setController(Controller::class);
        $this->addRegisterItems($setting);
        $settings->addSetting($setting);
    }

    private function addRegisterItems(SettingsEvent\Setting $setting): void
    {
        $setting->addInput([
            'name' => 'ticketing_autoclose_time',
            'type' => 'number',
            'min' => 0,
            'empty' => true,
            'zero' => true,
            'default' => 0,
        ]);
        $setting->addField([
            'name' => 'ticketing_autoclose_time',
            'type' => 'number',
            'label' => t('settings.ticketing.autoclose_time'),
            'min' => 1,
            'ltr' => true,
            'placeholder' => t('settings.ticketing.autoclose_time.disable'),
            'input-group' => [
                'right' => [
                    [
                        'type' => 'addon',
                        'text' => t('packages.ticketing.hour'),
                    ],
                ],
            ],
        ]);
        $options = Options::get('packages.ticketing.close.respitetime');
        $setting->setDataForm('ticketing_autoclose_time', $options ? intval($options / 3600) : '');

        $setting->addInput([
            'name' => 'ticketing_send_notification_on_send_ticket',
            'type' => 'bool',
        ]);
        $setting->addField([
            'name' => 'ticketing_send_notification_on_send_ticket',
            'type' => 'radio',
            'label' => t('settings.ticketing.send_notification_on_send_ticket'),
            'inline' => true,
            'options' => [
                [
                    'label' => t('ticketing.active'),
                    'value' => 1,
                ],
                [
                    'label' => t('ticketing.deactive'),
                    'value' => 0,
                ],
            ],
        ]);
        $options = Options::get('packages.ticketing.send_notification_on_send_ticket');
        $setting->setDataForm('ticketing_send_notification_on_send_ticket', $options ? 1 : 0);
    }
}
