<?php

namespace packages\ticketing\Listeners;

use packages\sms\Events\Templates;
use packages\ticketing\TicketMessage;
use packages\ticketing\Events\Tickets;

class SMS
{
    public function templates(Templates $event)
    {
        $event->addTemplate($this->newTicketTemplate());
        $event->addTemplate($this->replyTicketTemplate());
    }

    private function newTicketTemplate()
    {
        $template = new Templates();
        $template->name = 'ticketing_ticket_add';
        $template->event = Tickets\Add::class;
        $template->addVariable(TicketMessage::class);

        return $template;
    }

    private function replyTicketTemplate()
    {
        $template = new Templates();
        $template->name = 'ticketing_ticket_reply';
        $template->event = Tickets\Reply::class;
        $template->addVariable(TicketMessage::class);

        return $template;
    }
}
