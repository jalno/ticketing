<?php

namespace themes\clipone\Views\Ticketing;

use packages\base\Translator;
use packages\ticketing\Views\Inprogress as TicketInProgress;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\ViewTrait;

class Inprogress extends TicketInProgress
{
    use ViewTrait;
    use FormTrait;
    protected $ticket;

    public function __beforeLoad()
    {
        $this->ticket = $this->getTicket();
        $this->setTitle([
            t('ticket.inprogress'),
        ]);
        $this->addBodyClass('ticket-inprogress');
        $this->setNavigation();
    }

    private function setNavigation()
    {
        Navigation::active('ticketing/list');
    }
}
