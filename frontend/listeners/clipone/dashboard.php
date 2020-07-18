<?php
namespace themes\clipone\listeners\ticketing;
use packages\userpanel;
use packages\base\{db, translator, db\parenthesis};
use packages\ticketing\{Authorization, Authentication, ticket, ticket_message};
use themes\clipone\views\dashboard as view;
use themes\clipone\views\dashboard\shortcut;
class dashboard{
	public function initialize(){
		$this->addShortcuts();
	}
	protected function addShortcuts(){
		if (Authorization::is_accessed("list")) {
			$ticket = new ticket();
			$types = authorization::childrenTypes();
			$user = Authentication::getUser();
			$count = 0;
			$text = t("ticketing.active_ticket_shortcut_title.client");
			$url = userpanel\url("ticketing", array("unread" => 1));
			if ($types) {
				db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "INNER");
				$ticket->where("userpanel_users.type", $types, 'IN');
				$ticket->where("ticketing_tickets.status", array(Ticket::unread, Ticket::read, Ticket::in_progress), "IN");
				$count = $ticket->count();
				$text = t("shortcut.tickets.not.answered");
				$url = userpanel\url("ticketing", array(
					"status" => implode(",", array(Ticket::unread, Ticket::read, Ticket::in_progress)),
				));
			} else {
				db::join("ticketing_tickets_msgs", "ticketing_tickets_msgs.ticket=ticketing_tickets.id", "INNER");
				db::joinWhere("ticketing_tickets_msgs", "ticketing_tickets_msgs.status", Ticket_message::unread);
				$ticket->where("ticketing_tickets.client", Authentication::getID());
				$count = $ticket->count();
			}
			$shortcut = new shortcut("tickets");
			$shortcut->icon = "clip-user-6";
			if ($count and !$types) {
				$shortcut->color = Shortcut::Danger;
			}
			if ($count) {
				$shortcut->title = $count;
				$shortcut->text = $text;
				$shortcut->setLink(t("shortcut.tickets.link"), $url);
			} else {
				$shortcut->text = t("shortcut.tickets.has.question");
				$shortcut->setLink(t("shortcut.tickets.send.ticket"), userpanel\url("ticketing/new"));
			}
			view::addShortcut($shortcut);
		}
	}
}
