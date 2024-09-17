<?php

namespace themes\clipone\Views\Ticketing;

use packages\base\Translator;
use packages\ticketing\Views\Delete as TicketDelete;
use packages\userpanel;
use themes\clipone\Breadcrumb;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\ListTrait;
use themes\clipone\ViewTrait;

class Delete extends TicketDelete
{
    use ViewTrait;
    use ListTrait;
    protected $messages;

    public function __beforeLoad()
    {
        $this->setTitle([
            t('ticket.delete.warning.title'),
            '#'.$this->getTicketData()->id,
        ]);
        $this->setShortDescription(t('ticket.delete.warning.title'));
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
        $item->setTitle(t('ticket.delete.warning.title'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('fa fa-trash-o tip');
        Breadcrumb::addItem($item);

        Navigation::active('ticketing/list');
    }
}
