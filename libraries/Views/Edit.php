<?php

namespace packages\ticketing\Views;

use packages\ticketing\Ticket;

class Edit extends Form
{
    public function setTicket(Ticket $ticket)
    {
        $this->setData($ticket, 'ticket');
        $this->setDataForm($ticket->toArray());
    }

    public function getTicket()
    {
        return $this->getData('ticket');
    }

    public function setDepartment($department)
    {
        $this->setData($department, 'department');
    }

    public function getDepartment()
    {
        return $this->getData('department');
    }
}
