<?php
namespace packages\ticketing\listeners;
use \packages\notifications\events;
use \packages\ticketing\events as ticketingEevents;
class notifications{
	public function events(events $events){
		$events->add(ticketingEevents\tickets\add::class);
		$events->add(ticketingEevents\tickets\reply::class);
		$events->add(ticketingEevents\tickets\close::class);
	}
}