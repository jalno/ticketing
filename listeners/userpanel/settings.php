<?php
namespace packages\ticketing\listeners\userpanel;
use \packages\base\packages;
use \packages\base\translator;
use \packages\userpanel\events\settings as settingsEvent;
use \packages\ticketing\ticket_message;
use \packages\ticketing\controllers\userpanel\settings as controller;
class settings{
	public function settings_list(settingsEvent $settings){
		$tuning = new settingsEvent\tuning("ticketing");
		$tuning->setController(controller::class."@store");
		$tuning->addInput([
			'name' => 'ticketing_editor',
			'type' => 'string',
			'values' => [ticket_message::html, ticket_message::markdown]
		]);
		$tuning->addField([
			'name' => 'ticketing_editor',
			'type' => 'radio',
			'label' => translator::trans("ticketing.usersettings.message.editor.type"),
			'options' => [
				[
					'label' => translator::trans("ticketing.usersettings.message.editor.type.".ticket_message::html),
					'value' => ticket_message::html
				],
				[
					'label' => translator::trans("ticketing.usersettings.message.editor.type.".ticket_message::markdown),
					'value' => ticket_message::markdown
				]
			]
		]);
		$tuning->setDataForm('ticketing_editor', $settings->getUser()->option('ticketing_editor') ? $settings->getUser()->option('ticketing_editor') : ticket_message::html);
		$settings->addTuning($tuning);
	}
}
