<?php

namespace themes\clipone\Views\Ticketing;

use packages\base\Translator;
use packages\ticketing\Views\Lock as TicketLock;
use packages\userpanel;
use themes\clipone\Breadcrumb;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\ListTrait;
use themes\clipone\ViewTrait;

class Lock extends TicketLock
{
    use ViewTrait;
    use ListTrait;
    protected $messages;
    protected $canSend = true;

    public function __beforeLoad()
    {
        $this->setTitle([
            Translator::trans('ticket.lock'),
            '#'.$this->getTicketData()->id,
        ]);
        $this->setShortDescription(Translator::trans('ticket.lock'));
        $this->setNavigation();
    }

    private function setNavigation()
    {
        $item = new MenuItem('ticketing');
        $item->setTitle(Translator::trans('ticketing'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('clip-user-6');
        Breadcrumb::addItem($item);

        $item = new MenuItem('ticketing.lock');
        $item->setTitle(Translator::trans('ticket.lock'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('fa fa-ban tip');
        Breadcrumb::addItem($item);

        Navigation::active('ticketing/list');
    }
}
