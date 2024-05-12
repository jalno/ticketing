<?php

namespace packages\ticketing\Logs\Labels;

use packages\base\View;
use packages\ticketing\Contracts\ILabel;
use packages\userpanel\Log;
use packages\userpanel\Logs;
use packages\userpanel\User;

class Add extends Logs
{
    public static function create(ILabel $label, ?User $user = null): void
    {
        $log = new Log();
        $log->user = $user;
        $log->title = t('ticketing.logs.labels.add', [
            'id' => $label->getID(),
        ]);
        $log->type = self::class;
        $log->parameters = [
            'label' => $label->toArray(),
        ];

        $log->save();
    }

    public function getColor(): string
    {
        return 'circle-green';
    }

    public function getIcon(): string
    {
        return 'fa fa-tag';
    }

    public function buildFrontend(View $view): void
    {
    }
}
