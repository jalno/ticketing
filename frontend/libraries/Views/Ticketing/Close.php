<?php

namespace themes\clipone\Views\Ticketing;

use packages\base\Translator;
use packages\ticketing\Views\Close as TicketClose;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\ViewTrait;

class Close extends TicketClose
{
    use ViewTrait;
    use FormTrait;
    protected $ticket;

    public function __beforeLoad()
    {
        $this->ticket = $this->getTicket();
        $this->setTitle([
            Translator::trans('ticket.close'),
        ]);
        $this->addBodyClass('ticket-close');
        $this->setNavigation();
    }

    private function setNavigation()
    {
        Navigation::active('ticketing/list');
    }
}
