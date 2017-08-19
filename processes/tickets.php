<?php
namespace packages\ticketing\processes;
use \packages\base\log;
use \packages\base\options;
use \packages\base\process;
use \packages\base\response;
use \packages\base\NotFound;
use \packages\userpanel\date;
use \packages\ticketing\ticket;
use \packages\ticketing\events;
class tickets extends process{
	public function autoClose($data):response{
		log::setLevel('debug');
		$log = log::getInstance();
		$response = new response();
		$response->setStatus(false);
		$log->debug('looking for respite close time in options');
		$respiteTime = options::get('packages.ticketing.close.respitetime');
		$log->reply($respiteTime, "second ");
		$log->debug('setting time');
		$time = date::time();
		$log->reply($time);
		$closeTime = ($time - $respiteTime);
		$log->debug("looking in answered tickets that have last reply smaller than {$closeTime}");
		$ticket = new ticket();
		$ticket->where('status', ticket::answered);
		$ticket->where('reply_at', $closeTime, "<");
		$tickets = $ticket->get();
		$log->reply(count($tickets), " tickets found");
		foreach($tickets as $ticket){
			$log->debug('close ticket #', $ticket->id);
			$ticket->status = ticket::closed;
			$ticket->save();
			$log->debug("try send close notification trigger");
			$event = new events\tickets\close($ticket);
			$event->trigger();
			$log->reply('success');
		}
		$response->setStatus(true);

		return $response;
	}
}