<?php
namespace themes\clipone\listeners\ticketing;
use packages\base\{db, translator, db\parenthesis};
use packages\userpanel;
use packages\ticketing\{authorization, authentication, ticket, ticket_message};
use themes\clipone\views\dashboard as view;
use themes\clipone\views\dashboard\shortcut;
class dashboard{
	public function initialize(){
		$this->addShortcuts();
	}
	protected function addShortcuts(){
		if (authorization::is_accessed("list")) {
			$ticket = new ticket();
			$types = authorization::childrenTypes();
			db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "INNER");
			db::join("ticketing_tickets_msgs", "ticketing_tickets_msgs.ticket=ticketing_tickets.id", "INNER");
			if ($types) {
				$ticket->where("userpanel_users.type", $types, 'in');
			} else {
				$ticket->where("userpanel_users.id", authentication::getID());
			}
			$ticket->where("ticketing_tickets.status", array(ticket::answered, ticket::closed), "NOT IN");
			$tickets = $ticket->count();
			$shortcut = new shortcut("tickets");
			$shortcut->icon = "clip-user-6";
			if ($tickets) {
				$shortcut->title = $tickets;
				$shortcut->text = translator::trans("shortcut.tickets.not.answered");
				$shortcut->setLink(translator::trans("shortcut.tickets.link"), userpanel\url("ticketing"));
			} else {
				$shortcut->text = translator::trans("shortcut.tickets.has.question");
				$shortcut->setLink(translator::trans("shortcut.tickets.send.ticket"), userpanel\url("ticketing/new"));
			}
			view::addShortcut($shortcut);
		}
	}
}
