<?php
namespace packages\ticketing\controllers\userpanel;
use packages\base\{translator, inputValidation};
use packages\userpanel\{user, events\settings\Controller, events\settings\Log};
use packages\ticketing\ticket_message;

class settings implements Controller {
	public function store(array $inputs, user $user): array {
		$logs = array();
		$oldValue = $user->option("ticketing_editor");
		if (!$oldValue) {
			$oldValue = ticket_message::html;
		}
		if (isset($inputs["ticketing_editor"]) and $oldValue != $inputs["ticketing_editor"]) {
			$logs[] = new Log("ticketing_editor", $this->getEditorTitleById($oldValue), $this->getEditorTitleById($inputs["ticketing_editor"]), translator::trans("ticketing.usersettings.message.editor.type"));
			$user->setOption("ticketing_editor", $inputs["ticketing_editor"]);
		}
		return $logs;
	}
	private function getEditorTitleById(string $name) {
		return translator::trans("ticketing.usersettings.message.editor.type." . $name);
	}
}
