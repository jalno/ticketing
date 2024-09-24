<?php

namespace themes\clipone\Listeners\Ticketing;

use packages\base\View;
use packages\ticketing;
use packages\ticketing\Authorization;
use packages\userpanel;
use packages\userpanel\Authentication;
use themes\clipone\Events;
use themes\clipone\Tab;
use themes\clipone\Views;

class UserProfileTabs
{
    public function handle(Events\InitTabsEvent $event)
    {
        $view = $event->getView();
        if ($view instanceof Views\Users\View) {
            $userID = $view->getData('user')->id;
            if (Authorization::is_accessed('list') and $userID != Authentication::getID()) {
                $this->addTicketsToUserProfile($view);
            }
        }
    }

    private function addTicketsToUserProfile(View $view)
    {
        $userID = $view->getData('user')->id;
        $tabView = View::byName(Views\Ticketing\ListView::class);
        $tabView->setNewTicketClientID($userID);
        $tabView->isTab(true);
        $tab = new Tab('ticket', $tabView);
        $tab->setTitle(t('tickets'));
        $tab->setLink(userpanel\url('users/tickets/'.$userID));
        $view->addTab($tab);
    }
}
