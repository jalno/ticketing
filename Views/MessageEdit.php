<?php

namespace packages\ticketing\Views;

class MessageEdit extends Form
{
    public function setMessageData($message)
    {
        $this->setData($message, 'message');
        $this->setDataForm($message->toArray());
    }

    public function getMessageData()
    {
        return $this->getData('message');
    }
}
