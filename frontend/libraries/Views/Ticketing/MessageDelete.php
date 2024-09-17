<?php

namespace themes\clipone\Views\Ticketing;

use packages\base\Translator;
use packages\userpanel;
use themes\clipone\Breadcrumb;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\ListTrait;
use themes\clipone\ViewTrait;

class MessageDelete extends \packages\ticketing\Views\MessageDelete
{
    use ViewTrait;
    use ListTrait;
    protected $networks = [];
    protected $lastlogin = 0;
    protected $logs;

    public function __beforeLoad()
    {
        $this->setTitle([
            t('ticketing.delete'),
            t('ticket'),
            '#'.$this->getMessageData()->id,
        ]);
        $this->setShortDescription(t('message.delete.warning.title'));
        $this->setNavigation();
    }

    private function setNavigation()
    {
        $item = new MenuItem('ticketing');
        $item->setTitle(t('ticketing'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('clip-user-6');
        Breadcrumb::addItem($item);

        $item = new MenuItem('ticketing.delete');
        $item->setTitle(t('message.delete.warning.title'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('fa fa-trash-o');
        Breadcrumb::addItem($item);

        Navigation::active('ticketing/list');
    }
}
