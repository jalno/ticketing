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
            t('ticket.unlock'),
            '#'.$this->getTicketData()->id,
        ]);
        $this->setShortDescription(t('ticket.unlock'));
        $this->setNavigation();
    }

    private function setNavigation()
    {
        $item = new MenuItem('ticketing');
        $item->setTitle(t('ticketing'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('clip-user-6');
        Breadcrumb::addItem($item);

        $item = new MenuItem('ticketing.unlock');
        $item->setTitle(t('ticket.unlock'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('fa fa-unlock tip');
        Breadcrumb::addItem($item);

        Navigation::active('ticketing/list');
    }
}
