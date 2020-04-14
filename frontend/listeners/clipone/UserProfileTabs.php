<?php
namespace themes\clipone\listeners\ticketing;

use packages\base\View;
use packages\userpanel;
use packages\userpanel\Authentication;
use packages\ticketing;
use packages\ticketing\Authorization;
use themes\clipone\{events, views, Tab};

class UserProfileTabs {
	public function handle(events\InitTabsEvent $event) {
		$view = $event->getView();
		if ($view instanceof views\Users\View) {
			$userID = $view->getData('user')->id;
			if (Authorization::is_accessed("list", "ticketing") and $userID != Authentication::getID()) {
				$this->addTicketsToUserProfile($view);
			}
		}
	}
	private function addTicketsToUserProfile(View $view) {
		$userID = $view->getData('user')->id;
		$tabView = View::byName(ticketing\views\ticketlist::class);
		$tabView->setNewTicketClientID($userID);
		$tabView->isTab(true);
		$tab = new Tab("ticket", $tabView);
		$tab->setTitle(t("tickets"));
		$tab->setLink(userpanel\url("users/tickets/" . $userID));
		$view->addTab($tab);
	}
}