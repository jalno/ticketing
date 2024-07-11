<?php

namespace packages\ticketing\Listeners\UserPanel;

use packages\base\Translator;
use packages\ticketing\Controllers\UserPanel\Settings as Controller;
use packages\ticketing\TicketMessage;
use packages\userpanel\Events\Settings as SettingsEvent;

class Settings
{
    public function settings_list(SettingsEvent $settings)
    {
        $tuning = new SettingsEvent\Tuning('ticketing', 'fa fa-user-o');
        $tuning->setController(Controller::class);
        $tuning->addInput([
            'name' => 'ticketing_editor',
            'type' => 'string',
            'values' => [TicketMessage::html, TicketMessage::markdown],
        ]);
        $tuning->addField([
            'name' => 'ticketing_editor',
            'type' => 'radio',
            'label' => Translator::trans('ticketing.usersettings.message.editor.type'),
            'options' => [
                [
                    'label' => Translator::trans('ticketing.usersettings.message.editor.type.'.TicketMessage::html),
                    'value' => TicketMessage::html,
                ],
                [
                    'label' => Translator::trans('ticketing.usersettings.message.editor.type.'.TicketMessage::markdown),
                    'value' => TicketMessage::markdown,
                ],
            ],
        ]);
        $tuning->setDataForm('ticketing_editor', $settings->getUser()->option('ticketing_editor') ? $settings->getUser()->option('ticketing_editor') : TicketMessage::html);
        $settings->addTuning($tuning);
    }
}
