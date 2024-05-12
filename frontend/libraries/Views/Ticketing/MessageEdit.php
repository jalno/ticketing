<?php

namespace themes\clipone\Views\Ticketing;

use packages\ticketing\Views\MessageEdit as MessagEdit;
use packages\userpanel;
use themes\clipone\Breadcrumb;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\FormTrait;
use themes\clipone\ViewTrait;

class MessageEdit extends MessagEdit
{
    use ViewTrait;
    use FormTrait;
    use HelperTrait;

    protected $message;
    protected $ticket;

    public function __beforeLoad()
    {
        $this->message = $this->getMessageData();
        $this->ticket = $this->message->ticket;

        $this->setTitle([
            t('ticketing.edit'),
            t('ticket'),
            '#'.$this->getMessageData()->id,
        ]);
        $this->setShortDescription(t('message.edit.notice.title'));
        $this->setNavigation();

        $this->initFormData();
    }

    private function setNavigation()
    {
        $item = new MenuItem('ticketing');
        $item->setTitle(t('ticketing'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('clip-user-6');
        Breadcrumb::addItem($item);
        $item = new MenuItem('ticketing.edit');
        $item->setTitle(t('message.edit.notice.title'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('fa fa-edit tip tooltips');
        Breadcrumb::addItem($item);
        Navigation::active('ticketing/list');
    }

    public function initFormData()
    {
        if (!$this->getDataForm('message_format')) {
            $this->setDataForm($this->message->format, 'message_format');
        }
        if (!$this->getDataForm('content')) {
            $this->setDataForm($this->message->text, 'content');
        }
    }
}
