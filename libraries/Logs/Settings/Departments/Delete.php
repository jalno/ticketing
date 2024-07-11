<?php

namespace packages\ticketing\Logs\Settings\Departments;

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
        return 'fa fa-bank';
    }

    public function buildFrontend(View $view)
    {
    }
}
