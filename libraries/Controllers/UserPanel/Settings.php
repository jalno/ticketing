<?php

namespace packages\ticketing\Controllers\UserPanel;

use packages\base\Translator;
use packages\ticketing\TicketMessage;
use packages\userpanel\Events\Settings\Controller;
use packages\userpanel\Events\Settings\Log;
use packages\userpanel\User;

class Settings implements Controller
{
    public function store(array $inputs, User $user): array
    {
        $logs = [];
        $oldValue = $user->option('ticketing_editor');
        if (!$oldValue) {
            $oldValue = TicketMessage::html;
        }
        if (isset($inputs['ticketing_editor']) and $oldValue != $inputs['ticketing_editor']) {
            $logs[] = new Log('ticketing_editor', $this->getEditorTitleById($oldValue), $this->getEditorTitleById($inputs['ticketing_editor']), t('ticketing.usersettings.message.editor.type'));
            $user->setOption('ticketing_editor', $inputs['ticketing_editor']);
        }

        return $logs;
    }

    private function getEditorTitleById(string $name)
    {
        return t('ticketing.usersettings.message.editor.type.'.$name);
    }
}
