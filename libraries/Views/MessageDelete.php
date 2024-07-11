<?php

namespace packages\ticketing\Views;

use packages\ticketing\Authorization;

class MessageDelete extends \packages\ticketing\View
{
    protected $canDel;
    protected static $navigation;

    public function __construct()
    {
        $this->canDel = Authorization::is_accessed('delete');
    }

    public function setMessageData($data)
    {
        $this->setData($data, 'message');
    }

    public function getMessageData()
    {
        return $this->getData('message');
    }
}
