<?php

namespace packages\ticketing\Views;

use packages\ticketing\Ticket;

class Inprogress extends Form
{
    public function setTicket(Ticket $ticket)
    {
        $this->setData($ticket, 'ticket');
    }

    public function getTicket(): Ticket
    {
        return $this->getData('ticket');
    }
}
