<?php

namespace packages\ticketing\logs\labels;

use packages\base\View;
use packages\ticketing\contracts\ILabel;
use packages\userpanel\Log;
use packages\userpanel\Logs;
use packages\userpanel\User;

class Edit extends Logs
{
    public static function create(ILabel $label, ILabel $origin, ?User $user = null): void
    {
        $changes = $label->getChanges($origin);

        if (!isset($changes['new']) or empty($changes['new'])) {
            return;
        }

        $log = new Log();
        $log->user = $user;
        $log->title = t('ticketing.logs.labels.edit', [
            'id' => $label->id,
        ]);
        $log->type = self::class;
        $log->parameters = $changes;

        $log->save();
    }

    public function getColor(): string
    {
        return 'circle-teal';
    }

    public function getIcon(): string
    {
        return 'fa fa-tag';
    }

    public function buildFrontend(View $view): void
    {
    }
}
