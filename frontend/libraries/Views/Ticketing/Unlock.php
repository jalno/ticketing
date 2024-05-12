<?php

namespace themes\clipone\Views\Ticketing;

use packages\base\Translator;
use packages\ticketing\Views\Unlock as TicketUnlock;
use packages\userpanel;
use themes\clipone\Breadcrumb;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\ListTrait;
use themes\clipone\ViewTrait;

class Unlock extends TicketUnlock
{
    use ViewTrait;
    use ListTrait;
    protected $messages;

    public function __beforeLoad()
    {
        $this->setTitle([
            Translator::trans('ticket.unlock'),
            '#'.$this->getTicketData()->id,
        ]);
        $this->setShortDescription(Translator::trans('ticket.unlock'));
        $this->setNavigation();
    }

    private function setNavigation()
    {
        $item = new MenuItem('ticketing');
        $item->setTitle(Translator::trans('ticketing'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('clip-user-6');
        Breadcrumb::addItem($item);

        $item = new MenuItem('ticketing.unlock');
        $item->setTitle(Translator::trans('ticket.unlock'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('fa fa-unlock tip');
        Breadcrumb::addItem($item);

        Navigation::active('ticketing/list');
    }
}
