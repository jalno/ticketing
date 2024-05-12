<?php
namespace packages\ticketing\Listeners;
use \packages\notifications\Events;
use \packages\ticketing\Events as TicketingEevents;
class Notifications{
	public function events(Events $events){
		$events->add(TicketingEevents\Tickets\Add::class);
		$events->add(TicketingEevents\Tickets\Reply::class);
		$events->add(TicketingEevents\Tickets\Close::class);
		$events->add(TicketingEevents\Tickets\Inprogress::class);
	}
}