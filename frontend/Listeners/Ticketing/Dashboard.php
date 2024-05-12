<?php
namespace themes\clipone\Listeners\Ticketing;

use packages\ticketing\{Authorization, Ticket};
use themes\clipone\Views\{Dashboard as View, Dashboard\Shortcut};
use function packages\userpanel\url;

class Dashboard {

	public function initialize() {
		$this->addShortcuts();
	}

	protected function addShortcuts() {
		if (!Authorization::is_accessed("list")) {
			return;
		}
		$types = Authorization::childrenTypes();
		$count = Ticket::countUnreadTicketCountByUser();
		$shortcut = new Shortcut("tickets");
		$shortcut->icon = "clip-user-6";
		if ($count and !$types) {
			$shortcut->color = Shortcut::Danger;
		}
		if ($count) {
			if ($types) {
				$shortcut->text = t("shortcut.tickets.not.answered");
				$url = url("ticketing", array(
					"status" => implode(",", [Ticket::unread, Ticket::read, Ticket::in_progress]),
				));
			} else {
				$shortcut->text = t("ticketing.active_ticket_shortcut_title.client");
				$url = url("ticketing", array("unread" => 1));
			}
			$shortcut->title = $count;
			$shortcut->setLink(t("shortcut.tickets.link"), $url);
		} else {
			$shortcut->text = t("shortcut.tickets.has.question");
			$shortcut->setLink(t("shortcut.tickets.send.ticket"), url("ticketing/new"));
		}
		View::addShortcut($shortcut);
	}
}
