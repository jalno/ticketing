<?php

namespace packages\ticketing\Logs\Tickets;

use packages\base\View;
use packages\userpanel\Logs;

class Delete extends Logs
{
    public function getColor(): string
    {
        return 'circle-bricky';
    }

    public function getIcon(): string
    {
        return 'fa fa-ticket';
    }

    public function buildFrontend(View $view)
    {
    }
}
