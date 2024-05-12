<?php

namespace packages\ticketing\Views;

class Unlock extends \packages\ticketing\View
{
    public function setTicketData($data)
    {
        $this->setData($data, 'ticket');
    }

    public function getTicketData()
    {
        return $this->getData('ticket');
    }
}
