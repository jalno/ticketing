<?php

namespace packages\ticketing;

use packages\userpanel\Authorization as UserPanelAuthorization;

class Authorization extends UserPanelAuthorization
{
    public static function is_accessed($permission, $prefix = 'ticketing')
    {
        return parent::is_accessed($permission, $prefix);
    }

    public static function haveOrFail($permission, $prefix = 'ticketing')
    {
        parent::haveOrFail($permission, $prefix);
    }
}
