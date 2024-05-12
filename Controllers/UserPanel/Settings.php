<?php
namespace packages\ticketing\Controllers\UserPanel;
use packages\base\{Translator, InputValidation};
use packages\userpanel\{User, Events\Settings\Controller, Events\Settings\Log};
use packages\ticketing\TicketMessage;

class Settings implements Controller {
	public function store(array $inputs, User $user): array {
		$logs = array();
		$oldValue = $user->option("ticketing_editor");
		if (!$oldValue) {
			$oldValue = TicketMessage::html;
		}
		if (isset($inputs["ticketing_editor"]) and $oldValue != $inputs["ticketing_editor"]) {
			$logs[] = new Log("ticketing_editor", $this->getEditorTitleById($oldValue), $this->getEditorTitleById($inputs["ticketing_editor"]), Translator::trans("ticketing.usersettings.message.editor.type"));
			$user->setOption("ticketing_editor", $inputs["ticketing_editor"]);
		}
		return $logs;
	}
	private function getEditorTitleById(string $name) {
		return Translator::trans("ticketing.usersettings.message.editor.type." . $name);
	}
}
